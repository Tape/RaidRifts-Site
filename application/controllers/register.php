<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Register extends CI_Controller
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
		$errors = array();
		if($data && IS_VALID_AJAX) {
			//Verify the username for length, and then verify against the database.
			if(strlen($data['username']) < 2) {
				$errors[] = "Your username was too short (2+ chars).";
			} elseif(strlen($data['username']) > 16) {
				$errors[] = "Your username was too long (16- chars).";
			} else {
				$q = $this->db->query("SELECT COUNT(*) as total FROM users WHERE `username` = {$this->db->escape(strtolower($data['username']))}");
				if($q->row()->total > 0) $errors[] = "This username is already taken.";
			}
			
			//Also verify that the password is the minimum length.
			if(strlen($data['password1']) < 6) {
				$errors[] = "Your password is too short (6+ chars).";
			}
			//Verify the user's passwords match.
			if($data['password1'] != $data['password2']) {
				$errors[] = "Your passwords did not match.";
			}
			
			//Verify that the email is the correct format, then verify against the database.
			if(!strlen($data['email']) || !preg_match("/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/", $data['email'])) {
				$errors[] = "The email provided is invalid.";
			} else {
				$data['email'] = strtolower($data['email']);
				$q = $this->db->query("SELECT COUNT(*) as total FROM users WHERE `email` = {$this->db->escape($data['email'])}");
				if($q->row()->total > 0) $errors[] = "This email address is already taken.";
			}
			
			//If there are errors output them for the client to fix.
			if(sizeof($errors)) {
				$this->output->set_output(implode('<br />', $errors));
			} else {
				//Prepare last stuff.
				$pw = gen_password($data['password1']);
				//We now want to email the user and validate their account.
				$this->load->library('email');
				$this->email->from('webmaster@raidrifts.com', 'RaidRifts');
				$this->email->to($data['email']);
				$this->email->subject('Welcome to RaidRifts!');
				
				//Build the email message.
				$base_url = base_url();
				$msg = "Welcome to Raidrifts, {$data['username']}!\n\n";
				$msg .= "You must validate your account in order to use it. Simply visit {$base_url}register/complete/{$this->verify_gen($data['username'], $pw)}/{$data['username']}/ to verify your account.";
				$this->email->message($msg);
				if($this->email->send()) {
					//This means all data was valid, insert into the database.
					$this->db->insert("users", array(
						'username' => $data['username'],
						'password' => $pw,
						'email' => $data['email'],
						'added' => date("Y-m-d H:i:s")
					));
					//Output to the user everything went well.
					$this->output->set_output('SUCCESS');
				} else {
					$this->output->set_output("An error occurred while processing.");
				}
			}
		} else {
			show_404("invalid_registration_request");
		}
	}
	
	public function complete($verification = null, $username = null)
	{
		$data = array();
		//If the user data isn't supplied correctly we don't care.
		if(is_null($verification) || is_null($username)) {
			$data['body'] = 'pages/register_view';
		//If it is, check if its correct.
		} else {
			//First grab the userdata from the database to produce the hash.
			$q = $this->db->get_where('users', array('LOWER(`username`)' => strtolower($username)));
			if($q->num_rows()) {
				$user_data = $q->row();
				//See if the verification code is right.
				if($verification === $this->verify_gen($user_data->username, $user_data->password)) {
					$this->db->update('users', array('verified' => 1), array('id' => $user_data->id));
					$data['body'] = 'pages/register_complete';
				}
			}
			
			//Error document if any of the statements failed.
			if(!isset($data['body'])) $data['body'] = 'pages/register_error';
		}
		$this->load->view('template', $data);
	}
	
	private function verify_gen($username, $password)
	{
		return sha1($username.$password);
	}
}
