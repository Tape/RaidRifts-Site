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
			'eu_servers' => $this->browse_model->get_shards('eu')
		);
		$this->load->view('template', $data);
	}
	
	public function shard($id = null)
	{
		if(is_null($id)) show_404('no_shard_error');
		
		$data = array(
			'body' => 'browse/shard',
			'shard' => $this->browse_model->get_shard($id)
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
		$guild = $this->browse_model->get_guild($id, $year, $month);
		
		//Prep the data.
		$data = array(
			'body' => 'browse/calendar',
			'guild' => $guild
		);
		if($data === false) {
			show_404('guild_not_found');
		}
		$data['calendar'] = $this->calendar->generate($year, $month, $guild->calendar);
		$this->load->view('template', $data);
	}
}
