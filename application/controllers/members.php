<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Members extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('member_model');
		$this->load->helper('form');
	}
	
	public function controlpanel()
	{
		$post = $this->input->post();
		$model =& $this->member_model;
		if($post !== false && !empty($post['type'])) {
			switch($post['type']) {
			case 'edit-logs':
				if($model->edit_logs($post)) {
					$data['notification'] = 'Your log edits have been saved successfully.';
				} else {
					$data['error'] = 'An error has occurred while editing logs.';
				}
				break;
			case 'add-guild':
				if($model->add_guild($post)) {
					$data['notification'] = 'Your guild has been added successfully. You may now upload logs.';
				} else {
					$data['error'] = $model->get_error();
				}
				break;
			case 'account-settings':
				if($model->edit_settings($post)) {
					$data['notification'] = 'Your settings have been saved successfully.';
				} else {
					$data['error'] = $model->get_error();
				}
				break;
			case 'guild-settings':
				if($model->edit_guild_settings($post)) {
					$data['notification'] = 'Your guild settings have been saved successfully.';
				} else {
					$data['error'] = $model->get_error();
				}
				break;
			case 'manage-members':
				if($model->adjust_rank($post)) {
					$data['notification'] = 'Your guild ranks have been saved successfully.';
				} else {
					$data['error'] = $model->get_error();
				}
				break;
			case 'leave-guild':
				if($model->leave_guild()) {
					$data['notification'] = 'You have left your guild.';
				} else {
					$data['error'] = $model->get_error();
				}
				break;
			case 'apply':
				if($model->apply_to_guild($post, false)) {
					$data['notification'] = 'Your application was submitted.';
				} else {
					$data['error'] = $model->get_error();
				}
			}
		}

		//Prepare data.
		$data['shards'] = $model->get_shards();
		$data['guild_info'] = $model->get_guild_info();
		$data['applications'] = $model->get_applications();
		$data['members'] = $model->get_members('rank');
		if($this->user->has_permission(ACCESS_GUILD_LEADER)) $data['leader_change_list'] = $model->get_members('name', true);
		$data['body_vars'] = $model->get_logs();
		$data['body'] = 'members/control-panel_view';
		
		//Load the template.
		$this->load->view('template', $data);
	}
	
	public function ajax($action = null)
	{
		//If the link was reached incorrectly we want to show a 404.
		if(is_null($action) || !IS_VALID_AJAX) show_404('invalid_member_ajax');
		$data = $this->input->post();
		$model =& $this->member_model;

		switch($action) {
		case 'deletelog':
			$this->ajax_output($model->delete_log($data));
			break;
		case 'application':
			$this->ajax_output($model->handle_application($data));
			break;
		case 'kick':
			$this->ajax_output($model->remove_user($data));
			break;
		case 'rank':
			$model->adjust_rank($data);
			break;
		case 'guilds':
			$model->load_guilds($data);
			break;
		case 'apply':
			$model->apply_to_guild($data);
			break;
		default:
			$this->ajax_output(false);
			break;
		}
	}
	
	private function ajax_output($bool)
	{
		if($bool) $this->output->set_output('SUCCESS');
		else $this->output->set_output('ERROR');
	}
}
