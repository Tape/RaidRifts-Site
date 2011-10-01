<?php
class User
{
	//Vars for CodeIgniter.
	private $db;
	
	//Vars for website use.
	public $id;
	public $username;
	public $email;
	public $guild;
	public $admin;
	private $password;
	private $verified;
	private $joined;
	private $rank;
	private $added;
	
	public function __construct()
	{
		//We need CodeIgniter to load up everything we need.
		$ci =& get_instance();
		//Now we can make shorter references to the libraries we want.
		$this->db =& $ci->db;
		
		//Get the user's ID.
		$this->id = $ci->session->userdata('user_id');
		
		//Check if the user is logged in...
		if($this->id !== false) {
			$rs = $this->db->where('id', $this->id)->get('users');
			if($rs->num_rows() < 1) {
				//If there was no results destroy the session.
				$this->session->sess_destroy();
				$this->id = false;
			} else {
				//Otherwise store the userdata.
				$data = $rs->row_array();
				foreach($data as $field => $value) {
					//Clean up stuff that will be displayed publicly.
					if($field == 'username') {
						$this->$field = htmlspecialchars($value);
						continue;
					}
					$this->$field = $value;
				}
			}
			
			//Free the results.
			$rs->free_result();
		}
	}
	
	public function has_permission($permission)
	{
		return ($this->rank & $permission) > 0;
	}
	
	public function update_rank($rank, $db = true)
	{
		//Set the rank.
		$this->rank = $rank;
		
		//If we are updating the database.
		if($db) {
			$this->db->where('id', $this->id);
			$this->db->update('users', array('rank' => $this->rank));
			return $this->db->affected_rows() > 0;
		}
		return true;
	}
	
	public function leave_guild()
	{
		$this->db->where('id', $this->id);
		$this->db->update('users', array(
			'guild' => ($this->guild = 0),
			'rank' => ($this->rank = 0)
		));
		
		return $this->db->affected_rows() > 0;
	}
}
?>