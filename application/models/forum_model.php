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
		$this->db->select('boards.id, boards.id_category, boards.date_lastpost, boards.name, boards.description');
		$this->db->select('users.admin, boards.count_topics, boards.count_posts, users.username');
		$this->db->where('`boards`.`id_category` IN('.$in.')')->order_by('sort', 'asc');
		$this->db->ar_orderby[] = ' FIELD(`boards`.`id_category`, '.$in.')';
		$this->db->join('users', 'users.id = boards.id_lastpost_user', 'left');
		$rs = $this->db->get('boards');
		//Check if we have results.
		if($rs->num_rows() < 1) return false;
		
		//Loop through each board and put it in the correct category.
		foreach($rs->result() as $board) if(isset($data[$board->id_category])) {
			$data[$board->id_category]->boards[] = $board;
		}
		$rs->free_result();
		
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
	
	public function get_topic($id)
	{
		//Check if the board exists.
		$rs = $this->db->where('id', $id)->get('topics');
		if($rs->num_rows() < 1) show_404('invalid_forum_topic');
		
		//Fetch the necessary data and free the result.
		$data = $rs->row();
		$rs->free_result();
		
		//If we have gotten here then we know this is valid.
		$tmp = $this->db->get_where('boards', array('id' => $data->id_board))->row();
		$data->board_id = $tmp->id;
		$data->board_name = $tmp->name;
		return $data;
	}
	
	public function get_topics($id)
	{
		$this->db->ar_orderby[] = 'IFNULL(`topics`.`date_lastpost`, `topics`.`date_inserted`) DESC';
		$this->db->where('id_board', $id);
		$this->db->select('topics.*, users1.username, users1.admin, users2.username AS lastpost_username, users2.admin AS lastpost_admin');
		$this->db->join('users AS users1', 'users1.id = topics.id_user', 'inner');
		$this->db->join('users AS users2', 'users2.id = topics.id_lastpost_user', 'left');
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
		$this->db->select('topics.*, users.username, users.admin, users.joined, users.posts, boards.name');
		$this->db->join('boards', 'boards.id = topics.id_board', 'left');
		$this->db->where('topics.id', $id)->join('users', 'users.id = topics.id_user', 'left');
		$rs = $this->db->get('topics');
		if($rs->num_rows() < 1) return false;
		$data[] = $rs->row();
		$rs->free_result();
		
		//Now loop through the posts.
		$this->db->order_by('posts.date_inserted', 'asc')->where('id_topic', $id);
		$this->db->join('users', 'users.id = posts.id_user', 'inner');
		$rs = $this->db->get('posts');
		if($rs->num_rows() > 0) foreach($rs->result() as $row) $data[] = $row;
		$rs->free_result();
		
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
		if(!isset($data['body']) || $len == 0 || $len > 246000) {
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
		$topic_id = $this->db->insert_id();
		
		//We want to update the board with the most recent post too.
		$this->db->set('count_topics', 'count_topics + 1', false)->set('id_lastpost', 'NULL', false)->update('boards', array(
			'id_lastpost_user' => $this->user->id,
			'id_lasttopic' => $this->db->insert_id(),
			'date_lastpost' => $timestamp
		), array('id' => $board->id));
		
		//Lastly increment the user's post count.
		$this->db->set('posts', 'posts + 1', false)->update('users', array(
			'id_lasttopic' => $topic_id
		), array('id' => $this->user->id));
		
		return $topic_id;
	}
	
	public function create_post(&$data, &$topic)
	{
		//Loop through and clean up stuff.
		foreach($data as &$var) $var = trim($var);
		
		//Check if the title meets criteria.
		$len = strlen($data['title']);
		if($len > 128) {
			$this->error = 'The post name you have supplied is too long.';
			return false;
		}
		
		//Same for the body.
		$len = strlen($data['body']);
		if(!isset($data['body']) || $len == 0 || $len > 256000) {
			$this->error = 'You must fill out the message field or the message you have provided is too long.';
			return false;
		}
		
		//Cleanup each element.
		foreach($data as &$var) $var = htmlspecialchars($var, ENT_NOQUOTES);
		
		//Now we can submit the data to the database.
		$timestamp = date('Y-m-d H:i:s');
		$this->db->insert('posts', array(
			'id_topic' => $topic->id,
			'id_user' => $this->user->id,
			'title' => $data['title'],
			'body' => $data['body'],
			'date_inserted' => $timestamp
		));
		$post_id = $this->db->insert_id();
		
		//Update the topic with information about the post.
		$this->db->set('post_count', 'post_count + 1', false)->set('id_lastpost', $post_id, false)->update('topics', array(
			'id_lastpost_user' => $this->user->id,
			'date_lastpost' => $timestamp,
			'id_lastpost' => $post_id
		), array('id' => $topic->id));
		
		//We want to update the board with the most recent post too.
		$this->db->set('count_posts', 'count_posts + 1', false)->set('id_lastpost', $post_id, false)->update('boards', array(
			'id_lastpost_user' => $this->user->id,
			'id_lasttopic' => $topic->id,
			'date_lastpost' => $timestamp,
			'id_lastpost' => $post_id
		), array('id' => $topic->id_board));
		
		//Lastly increment the user's post count.
		$this->db->set('posts', 'posts + 1', false)->update('users', array(
			'id_lastpost' => $post_id
		), array('id' => $this->user->id));
		
		return $post_id;
	}
	
	public function get_error()
	{
		return $this->error;
	}
}
?>