<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(DIR_CLASS."logparser.class.php");

class Forum_model extends CI_Model
{
	private $error;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_boards()
	{
		//Fetch all of the categories.
		$rs = $this->db->select('id, name')->order_by('sort', 'asc')->get('categories');
		if($rs->num_rows() < 1) return false;
		
		//Store the categories into an array.
		$data = array();
		foreach($rs->result() as $category) {
			$data[$category->id] = $category;
			$category->boards = array();
		}
		$rs->free_result();
		
		//Escape all of the array keys.
		$in = implode(', ', array_keys($data));
		//Now we want to fetch boards for each category.
		$this->db->where('`id_category` IN('.$in.')')->order_by('sort', 'asc');
		$this->db->ar_orderby[] = ' FIELD(id_category, '.$in.')';
		$rs = $this->db->get('boards');
		//Check if we have results.
		if($rs->num_rows() < 1) return false;
		
		//Loop through each board and put it in the correct category.
		$names = array();
		foreach($rs->result() as $board) if(isset($data[$board->id_category])) {
			$index = count($data[$board->id_category]->boards);
			$data[$board->id_category]->boards[$index] = $board;
			$data[$board->id_category]->boards[$index]->lastpost_username = 'Unknown';
			if(!is_null($board->id_lastpost_user)) {
				$names[$board->id_lastpost_user][] =& $data[$board->id_category]->boards[$index];
			}
		}
		$rs->free_result();
		
		//Lastly, we want to find out who the last poster was.
		if(count($names) > 0) {
			$in = implode(', ', array_unique(array_keys($names)));
			$rs = $this->db->select('id, username')->where('`id` IN ('.$in.')')->get('users');
			
			//If we have names to place...
			if($rs->num_rows() > 0) {
				foreach($rs->result() as $user) foreach($names[$user->id] as &$board) $board->lastpost_username = $user->username;
			}
			$rs->free_result();
		}
		
		//Final cleanup: Unset empty categories.
		foreach($data as $id => &$category) if(empty($category->boards)) unset($data[$id]);
		
		return $data;
	}
	
	public function get_board($id)
	{
		//Check if the board exists.
		$rs = $this->db->where('id', $id)->get('boards');
		if($rs->num_rows() < 1) show_404('invalid_forum_board');
		
		//Fetch the necessary data and free the result.
		$data = $rs->row();
		$rs->free_result();
		return $data;
	}
	
	public function get_topics($id)
	{
		//Check if the board exists.
		$this->db->order_by('date_inserted', 'desc')->where('id_board', $id);
		$this->db->select('topics.id, topics.id_lastpost, topics.title, topics.post_count, topics.views, topics.date_inserted, topics.date_lastpost');
		$this->db->select('users.username AS author_name');
		$this->db->join('users', 'users.id = topics.id_user', 'inner');
		$rs = $this->db->get('topics');
		if($rs->num_rows() < 1) return false;
		
		//Fetch the necessary data and free the result.
		$data = array();
		foreach($rs->result() as $row) $data[] = $row;
		$rs->free_result();
		return $data;
	}
	
	public function get_posts($id)
	{
		$data = array();
		//Check if the topic exists.
		$topic_rs = $this->db->where('topics.id', $id)->join('boards', 'boards.id = topics.id_board', 'inner')->get('topics');
		if($topic_rs->num_rows() < 1) return false;
		
		//Now loop through the posts.
		$this->db->order_by('posts.date_inserted', 'desc')->where('id_topic', $id);
		$this->db->join('users', 'users.id = posts.id_user', 'inner');
		$rs = $this->db->get('posts');
		if($rs->num_rows() > 0) foreach($rs->result() as $row) $data[] = $row;
		$rs->free_result();
		
		//We want to put the topic information at the end so we can pop it.
		$data[] = $topic_rs->row();
		$topic_rs->free_result();
		return $data;
	}
	
	public function create_topic(&$data, &$board)
	{
		//Loop through and clean up stuff.
		foreach($data as &$var) $var = trim($var);
		
		//Check if the title meets criteria.
		$len = strlen($data['title']);
		if(!isset($data['title']) || $len == 0 || $len > 128) {
			$this->error = 'You must either supply a topic name or the name you have supplied is too long.';
			return false;
		}
		
		//Same for the body.
		$len = strlen($data['body']);
		if(!isset($data['body']) || $len == 0 || $len > 512000) {
			$this->error = 'You must fill out the message field or the message you have provided is too long.';
			return false;
		}
		
		//Cleanup each element.
		foreach($data as &$var) $var = htmlspecialchars($var, ENT_NOQUOTES);
		
		//Now we can submit the data to the database.
		$timestamp = date('Y-m-d H:i:s');
		$this->db->insert('topics', array(
			'id_board' => $board->id,
			'id_user' => $this->user->id,
			'title' => $data['title'],
			'body' => $data['body'],
			'date_inserted' => $timestamp
		));
		
		//We want to update the board with the most recent post too.
		$this->db->set('count_topics', 'count_topics + 1', false)->set('id_lastpost', 'NULL', false)->update('boards', array(
			'id_lastpost_user' => $this->user->id,
			'id_lasttopic' => $this->db->insert_id(),
			'date_lastpost' => $timestamp
		), array('id' => $board->id));
		
		return true;
	}
	
	public function get_error()
	{
		return $this->error;
	}
}
?>