<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('password');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		//Check if we are submitting data.
		$data = $this->input->post();
		if($data) {
			if(empty($data['email'])) {
				//Query the database for a match.
				$this->db->select('users.id, users.verified, users.username, users.guild, guilds.name')->join('guilds', 'guilds.id = users.guild', 'left')->where(array(
					'LOWER(users.username)' => strtolower($data['username']),
					'users.password' => gen_password($data['password'])
				));
				$query = $this->db->get('users');
				if($query->num_rows() > 0) {
					//We have a winner.
					$tmp = $query->row();
					if($tmp->verified) {
						$this->session->set_userdata('user_id', $tmp->id);
						$this->output->set_output("SUCCESS");
					} else {
						//Not verified.
						$this->output->set_output("Account is not verified");
					}
				} else {
					//No match found.
					$this->output->set_output("Login information incorrect");
				}
			} else {
				//Lowercase the email to compare it against the database.
				$data['email'] = strtolower($data['email']);
				//Generate a new password.
				$new = substr(md5(mt_rand()), 0, 10);
				$this->db->where('email', $data['email'])->update('users', array(
					'password' => gen_password($new)
				));
				
				//Check if we updated the row.
				if($this->db->affected_rows() > 0) {
					//Password was changed successfully. Now we want to email the user.
					$this->load->library('email');
					//Prepare data we need in the email...
					$rs = $this->db->select('username')->where('email', $data['email'])->get('users');
					
					$this->email->from('webmaster@raidrifts.com', 'RaidRifts');
					$this->email->to($data['email']);
					$this->email->subject('RaidRifts password reset');
					
					//Build message content.
					$msg = "An attempt to reset your password has been made. You can log in with the below information:\n\n";
					$msg .= "Username: ".$rs->row()->username."\n";
					$msg .= "Password: ".$new."\n\n";
					$msg .= "If you feel you have received this message incorrectly, please contact us!";
					$this->email->message($msg);
					
					//Send the email.
					if($this->email->send()) {
						$this->output->set_output('SUCCESS');
					} else {
						$this->output->set_output('Error sending email');
					}
				} else {
					$this->output->set_output('Email address invalid');
				}
			}
		} else {
			if($this->session->userdata('user_id')) {
				//We're logging out.
				$this->session->sess_destroy('user_id');
				//Load up libs to redirect the user back to their page.
				$this->load->helper('url');
				$this->load->library('user_agent');
				redirect($this->agent->referrer());
			} else {
				//Invalid access to the login.
				show_404("invalid_login_request");
			}
		}
	}
}
