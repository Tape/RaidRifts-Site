<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Browse extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('browse_model');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$data = array(
			'body' => 'browse/index',
			'na_servers' => $this->browse_model->get_shards('na'),
			'eu_servers' => $this->browse_model->get_shards('eu'),
			'raids' => $this->browse_model->get_raids()
		);
		$this->load->view('template', $data);
	}
	
	public function shard($id = null)
	{
		if(is_null($id)) show_404('no_shard_error');
		
		$data = array(
			'body' => 'browse/shard',
			'guilds' => $this->browse_model->get_guilds($id)
		);
		$this->load->view('template', $data);
	}
	
	public function boss($id = null, $offset = 1)
	{
		if(is_null($id)) show_404('no_shard_error');
		
		if(($data = $this->browse_model->get_attempts($id, $offset)) === false) show_404('invalid_boss_id');
		$this->load->helper('data');
		$data = array(
			'body' => 'browse/boss',
			'offset' => $offset,
			'boss_name' => $data->EN,
			'encounters' => $data->encounters
		);
		$this->load->view('template', $data);
	}
	
	public function guild($id = null, $year = null, $month = null)
	{
		if(is_null($id)) show_404('no_guild_error');
		
		//load the calendar class.
		$this->load->library('calendar');
		$this->calendar->initialize(array('next_prev_url' => base_url().'browse/guild/'.$id.'/'));
		
		//Prepare variables for processing.
		$year = is_null($year) ? date('Y') : $year;
		$month = is_null($month) ? date('n') : $month;
		$logs = $this->browse_model->get_logs($id, $year, $month);
		
		//Prep the data.
		$data = array(
			'body' => 'browse/calendar',
			'body_vars' => $this->browse_model->get_guild($id),
			'guild_id' => $id,
			'calendar' => $this->calendar->generate($year, $month, $logs)
		);
		if($data['body_vars'] === false) {
			show_404('guild_does_not_exist');
		}
		$this->load->view('template', $data);
	}
}
