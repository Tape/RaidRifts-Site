<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('data');
		$this->load->model('data_model');
	}

	public function view($log_id = null, $lower = null, $upper = null)
	{
		if(is_null($log_id)) show_404('log_does_not_exist');
		//Load up models used by all parts.
		$sidebar_data = $this->data_model->get_sidebar_data($log_id);
		if($sidebar_data === false) show_404('log_does_not_exist');
		
		//Check if this log is blocked for the user logged in.
		$valid = (!$sidebar_data->private || $this->user->admin || ($sidebar_data->private && ($this->user->guild == $sidebar_data->gid)));

		//Init with consistent stuff.
		$vars = array();
		$vars['log_id'] = $log_id;
		if($valid) {
			$vars['sidebar'] = 'report_sidebar';
			$vars['sidebar_vars'] = $sidebar_data;
		}

		//Are we viewing an actual log?
		if(!is_null($lower) && !is_null($upper)) {
			//Store display specific variables.
			if($valid) {
				//Load up the model and set up the data.
				$this->load->model("data_model");
				$data =& $this->data_model->getData($log_id, $lower, $upper);
				$data->guild_name = $sidebar_data->name;
				$data->date = $sidebar_data->raid_date;
				
				//Set the rest of the body variables.
				$vars['body'] = 'content/data_table';
				$vars['body_vars'] = $data;
				$vars['log_start'] = $lower;
				$vars['log_end'] = $upper;
			} else {
				$vars['body'] = 'content/private_log';
			}
		} else {
			$this->load->model('report_model');
			if($valid) {
				$vars['body'] = 'content/log_view';
				$vars['body_vars'] = array(
					'guild_name' => $this->report_model->getGuildName($sidebar_data->gid),
					'upload_date' => $sidebar_data->raid_date
				);
			} else {
				$vars['body'] = 'content/private_log';
			}
		}
	
		//Set up the template.
		$this->load->view('template', $vars);
	}
	
	public function tab()
	{
		if(IS_VALID_AJAX) {
			$data =& $this->data_model->getUserData(
				$this->input->get('l'),//	Log ID.
				$this->input->get('s'),//	Start of the log.
				$this->input->get('e'),//	End of the log.
				$this->input->get('n')//	Name of the user to parse.
			);
			$this->load->view('content/player_view', array('data' => $data));
		}
	}
}
