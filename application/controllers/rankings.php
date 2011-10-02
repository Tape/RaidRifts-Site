<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rankings extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('rankings_model');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$data = array(
			'body' => 'rankings/index',
			'raids' => $this->rankings_model->get_raids()
		);
		$this->load->view('template', $data);
	}
	
	public function boss($id = null, $offset = 1)
	{
		if(is_null($id)) show_404('no_shard_error');
		
		if(($data = $this->rankings_model->get_attempts($id, $offset)) === false) show_404('invalid_boss_id');
		$this->load->helper('data');
		$data = array(
			'body' => 'rankings/boss',
			'offset' => $offset,
			'boss_name' => $data->EN,
			'encounters' => $data->encounters
		);
		$this->load->view('template', $data);
	}
}
