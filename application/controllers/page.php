<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page extends MY_Controller
{
	private $bosses = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('page_model');
	}
	
	private function get_boss_id($name)
	{
		foreach($this->bosses as $boss_id => &$vars)
			if($vars->EN == $name) return $boss_id;
		
		return 0;
	}
	
	public function index()
	{
		/*//Store the bosses.
		$rs = $this->db->get('bosses');
		foreach($rs->result() as $res) $this->bosses[$res->id] = $res;
		$rs->free_result();
		
		$add = array();
		//Outer loop; Go through each log.
		$rs = $this->db->get('logs');
		$i = 1;
		foreach($rs->result() as $log) {
			$vars = json_decode($log->vars);
			$skip = false;
			if(!is_object($vars)) $vars = new stdClass;
			if(!isset($vars->bosses) || empty($vars->bosses)) {
				//Empty logs??
				$skip = true;
			}
			
			//Inner loop 1: Go through each boss.
			if(!$skip) foreach($vars->bosses as $boss_name => &$attempts) {
				//Ignore empty bosses.
				if(empty($attempts)) continue;
				
				//Get the boss id and loop through each attempt.
				$boss_id = $this->get_boss_id($boss_name);
				foreach($attempts as &$attempt) {
					echo $i++."\n";
					$add[] = array(
						'id_log' => (int)$log->id,
						'id_boss' => (int)$boss_id,
						'start' => (int)$attempt->s,
						'end' => (int)$attempt->e,
						'length' => (int)$attempt->l,
						'wipe' => (bool)$attempt->w
					);
				}
			}
			
			//Finally correct the vars column.
			if(!isset($vars->milestone)) $vars->milestone = array();
			
			//$this->db->update('logs', array(
			//	'vars' => json_encode($vars->milestone)
			//), array(
			//	'id' => $log->id
			//));
		}
		//$this->db->insert_batch('attempts', $add);*/
		
		//Render the page.
		$this->view('home');
	}
	
	public function edit($id = null)
	{
		if(!$this->user->admin || !is_numeric($id)) {
			show_404("invalid_edit_access");
		}
		
		$data = array();
		$post = $this->input->post();
		if($post !== false) {
			if($this->page_model->update_page($id, $post))
				$data['notification'] = "Saved changes.";
			else
				$data['error'] = "An error occurred while saving.";
		}
		
		$this->load->view('template', array(
			'content' => $this->load->view('pages/edit', array(
				'page' => $this->page_model->load_page($id)
			) + $data, true)));
	}

	public function view($name)
	{
		//Lets check and see if this is an editable page.
		$page = $this->page_model->load_page($name);
		//Data stuff.
		$data = array();
		if($page === false) {
			//Prepare the data.
			$data['body'] = 'pages/'.$name.'_view';
			
			//Verify the page exists.
			$path = APPPATH."views/pages/{$name}_view.php";
			if(!file_exists($path)) {
				show_404($path);
			}
		} else {
			$replace = '';
			if($this->user->admin) {
				$replace = $this->load->view('pages/edit_button', array(
					'id' => $page->id
				), true);
			}
			$content = str_replace('{edit}', $replace, $page->content);
			$data['content'] = $content;
		}
		//Check if form data from one of the pages exists.
		$post = $this->input->post();
		if($post !== false) {
			switch($name) {
			case 'contact':
				if($this->page_model->submit_contact_form($post)) {
					 $this->output->set_output("SUCCESS");
				} else {
					$this->output->set_output("ERROR");
				}
				break;
			}
		}
		
		if(!AJAX_REQUEST) {
			//Output the data.
			$this->load->view('template', $data);
		}
	}
}
