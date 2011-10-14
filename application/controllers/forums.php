<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forums extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('forum_model');
		$this->load->helper('forum');
	}
	
	public function index()
	{
		$vars = array(
			'boards' => $this->forum_model->get_boards()
		);
		$data = array(
			'content' => $this->load->view('forums/index', $vars, true)
		);
		//Output the template.
		$this->load->view('template', $data);
	}
	
	public function board($id = null)
	{
		if(is_null($id) || !is_numeric($id)) show_404('invalid_forum_board');
		
		//Test if the board exists.
		$board = $this->forum_model->get_board($id);
		
		//Prepare variables for the view.
		$vars = array(
			'board' => $board,
			'topics' => $this->forum_model->get_topics($board->id)
		);
		//Create data to be injected into the template.
		$data = array(
			'content' => $this->load->view('forums/board', $vars, true)
		);
		
		$this->load->view('template', $data);
	}
	
	public function topic($id = null)
	{
		if(is_null($id) || !is_numeric($id)) show_404('invalid_forum_board');
		
		//Test if the board exists.
		$posts = $this->forum_model->get_posts($id);
		if($posts === false) show_404('invalid_topic');
		
		//Prepare variables for the view.
		$vars = array(
			'topic' => $posts[0],
			'posts' => $posts
		);
		//Create data to be injected into the template.
		$data = array(
			'content' => $this->load->view('forums/topic', $vars, true)
		);
		
		$this->load->view('template', $data);
	}
	
	public function create($id = null)
	{
		if(is_null($id) || !is_numeric($id) || $this->user->id === false) show_404('invalid_forum_create');
		
		//This is a little check for if the board exists and stuff.
		$board = $this->forum_model->get_board($id);
		
		//Load up variables for the view.
		$vars = array(
			'board' => $board,
			'is_topic' => true,
			'id_board', $id
		);
		//If a post has been submitted we want to send the data to the model.
		if(($post = $this->input->post()) !== false) if(($topic_id = $this->forum_model->create_topic($post, $board)) === false) {
			//If errors were found.
			$vars['error'] = $this->forum_model->get_error();
			$vars['post'] = $post;
		} else {
			//We created a topic, so redirect to it.
			redirect('/forums/topic/'.$topic_id.'/');
		}
		
		//Create data to be injected into the template.
		$data = array();
		if($this->user->id !== false) {
			$data['content'] = $this->load->view('forums/post', $vars, true);
		} else {
			$data['content'] = $this->load->view('forums/permission', null, true);
		}
		
		$this->load->view('template', $data);
	}
	
	public function post($id = null)
	{
		if(is_null($id) || !is_numeric($id) || $this->user->id === false) show_404('invalid_forum_post');
		
		//This is a little check for if the topic exists and stuff.
		$topic = $this->forum_model->get_topic($id);
		
		//Load up variables for the view.
		$vars = array(
			'topic' => $topic,
			'is_topic' => false
		);
		//If a post has been submitted we want to send the data to the model.
		if(($post = $this->input->post()) !== false) if(($post_id = $this->forum_model->create_post($post, $topic)) === false) {
			//If errors were found.
			$vars['error'] = $this->forum_model->get_error();
			$vars['post'] = $post;
		} else {
			//We created a new post, so redirect to it.
			redirect('/forums/topic/'.$topic->id.'/#post'.$post_id);
		}
		
		//Create data to be injected into the template.
		$data = array();
		if($this->user->id !== false) {
			$data['content'] = $this->load->view('forums/post', $vars, true);
		} else {
			$data['content'] = $this->load->view('forums/permission', null, true);
		}
		
		$this->load->view('template', $data);
	}
}
