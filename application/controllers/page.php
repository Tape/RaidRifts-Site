<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page extends MY_Controller
{
	private $bosses = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	private function get_boss_id($name)
	{
		foreach($this->bosses as $boss_id => &$vars) foreach(array('EN', 'FR', 'DE') as $lang) {
			if($vars->$lang == $name) return $boss_id;
		}
		
		return 0;
	}
	
	public function index()
	{
		/*
		//Store the bosses.
		$rs = $this->db->get('bosses');
		foreach($rs->result() as $res) $this->bosses[$res->id] = $res;
		
		$add = array();
		//Outer loop; Go through each log.
		$rs = $this->db->get('logs');
		foreach($rs->result() as $log) {
			$vars = json_decode($log->vars);
			if(!is_object($vars) || !isset($vars->bosses) || empty($vars->bosses)) {
				//Empty logs??
				continue;
			}
			
			//Inner loop 1: Go through each boss.
			foreach($vars->bosses as $boss_name => $attempts) {
				//Ignore empty bosses.
				if(empty($attempts)) continue;
				
				//Get the boss id and loop through each attempt.
				$boss_id = $this->get_boss_id($boss_name);
				foreach($attempts as $attempt) {
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
			if(!isset($vars->milestone)) continue;
			
			$this->db->update('logs', array(
				'vars' => json_encode($vars->milestone)
			), array(
				'id' => $log->id
			));
		}
		$this->db->insert_batch('attempts', $add);
		*/
		
		//Render the page.
		$this->load->view('template', array(
			//'content' => "<pre>".print_r($add, true)
			'content' => $this->load->view('pages/home_view', null, true)
		));
	}

	public function view($name)
	{
		//Data stuff.
		$data = array('body' => 'pages/'.$name.'_view');
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
			//Verify the page exists.
			$path = APPPATH."views/pages/{$name}_view.php";
			if(!file_exists($path)) {
				show_404($path);
			}
	
			//Prepare data.
			if($name == 'control-panel') {
				$data['body_vars'] = $this->page_model->getPersonalLogs();
				$data['shards'] = $this->page_model->getShards();
			}
	
			//Output the data.
			$this->load->view('template', $data);
		}
	}
}
