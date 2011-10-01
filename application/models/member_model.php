<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Member_model extends CI_Model
{
	private $error = null;
	
	public function get_guild_info()
	{
		//Are we even logged in?
		$user_id = $this->user->id;
		if(!$user_id) return false;
		
		//Grab the guild data.
		$data = $this->db->where('id', $this->user->guild)->get('guilds');
		
		//Make sure we have data and then store it.
		if($data->num_rows() < 1) return false;
		return $data->row();
	}
	
	public function adjust_rank($data)
	{
		//Make sure the correct data is set.
		if(!isset($data['changes']) || empty($data['changes'])) {
			$this->error = 'No rank changes were saved.';
			return false;
		}
		
		//Check the correct permissions.
		if(!$this->user->has_permission(ACCESS_GUILD_PROMOTE)) {
			$this->error = 'You do not have permission to promote users.';
			return false;
		}
		
		//Update the rows.
		$rows = explode(';', $data['changes']);
		foreach($rows as $row) {
			$vars = explode(':', $row);
			$this->db->where('id', $vars[0])->update('users', array('rank' => $vars[1]));
		}
		return true;
	}
	
	public function apply_to_guild($data, $ajax = true)
	{
		//Check if we have a valid guild id.
		if(!isset($data['gid']) || !is_numeric($data['gid'])) {
			$error = 'Invalid guild id';
			if($ajax) $this->output->set_output($error);
			else $this->error = $error;
			return false;
		}
		
		//Check if this person has more applications.
		$rs = $this->db->select('COUNT(*) AS total')->where(array(
			'gid' => $data['gid'],
			'uid' => $this->user->id
		))->get('applications');
		if($rs->row()->total > 0) {
			$error = 'You have already applied.';
			if($ajax) $this->output->set_output($error);
			else $this->error = $error;
			return false;
		}
		
		//Finally submit the data.
		$this->db->insert('applications', array(
			'gid' => $data['gid'],
			'uid' => $this->user->id,
			'date' => date("Y-m-d H:i:s"),
			'message' => $data['message']
		));
		if($ajax) $this->output->set_output('SUCCESS');
		return true;
	}
	
	public function leave_guild()
	{
		//Check if they are in a guild.
		if($this->user->guild == 0) {
			$this->error = "You are not in a guild.";
			return false;
		}
		
		//If the user is not a guild master they can leave when they want.
		if(!$this->user->has_permission(ACCESS_GUILD_LEADER)) {
			return $this->user->leave_guild();
		}
		//Going to do this statement all in one to check if the user is the sole member.
		$num_members = $this->db->select('COUNT(*) AS total')->where('guild', $this->user->guild)->get('users')->row()->total;
		//User has to be the sole member.
		if($num_members > 1) {
			$this->error = 'You may not leave the guild as guild master unless you are the only member.';
			return false;
		} else {
			return $this->user->leave_guild();
		}
	}
	
	public function edit_guild_settings($data)
	{
		//Invalid permissions.
		if(!$this->user->has_permission(ACCESS_GUILD_LEADER)) return false;
		
		//First we want to check if guild leadership is to be passed.
		if(is_numeric($data['leader']) && $data['leader'] != 0) {
			//First update the new person to be leader.
			$this->db->where(array(
				'id' => $data['leader'],
				'guild' => $this->user->guild
			))->update('users', array('rank' => PERMISSION_GUILD_LEADER));
			
			//If this was successful we can update the old leader.
			if($this->db->affected_rows() > 0) return $this->user->update_rank(PERMISSION_GUILD_OFFICER);
		}
		
		//Unset unwanted post data and prep vars.
		unset($data['type']);
		unset($data['leader']);
		$errors = array();
		//Make sure the name isn't too short.
		if(strlen($data['name']) < 4) {
			$errors[] = 'The guild name you have provided is too short.';
		}
		
		//No errors, let's update.
		if(empty($errors)) {
			//If they have submitted this form we can assume they're the guild master, so get their id.
			$this->db->where('id', $this->user->guild)->update('guilds', $data);
			//Successful.
			if($this->db->affected_rows() > 0) return true;
			$errors[] = 'No guild settings were changed.';
		}
		
		$this->error = implode('<br />', $errors);
		return false;
	}
	
	public function handle_application($data)
	{
		//If there is no result they don't have permission.
		if(!$this->user->has_permission(ACCESS_GUILD_ADD)) return false;
		
		//It's very unlikely that the user account is deleted, but we must verify that this is the users application to handle.
		$rs = $this->db->select('uid')->where(array(
			'id' => $data['aid'],
			'gid' => $this->user->guild
		))->get('applications');
		
		//Invalid!
		if($rs->num_rows() < 1) return false;
		
		//Totally valid, finalize the request.
		$user = $rs->row();
		if($data['action'] == 'accept') $this->db->where('id', $user->uid)->update('users', array(
			'guild' => $this->user->guild,
			'rank' => PERMISSION_GUILD_MEMBER,
			'joined' => date("Y-m-d")
		));
		$this->db->where('uid', $user->uid)->delete('applications');
		return true;
	}
	
	public function get_applications()
	{
		//The user permission is passed through the controller.
		$guild_id = $this->user->guild;
		if(!$this->user->has_permission(ACCESS_GUILD_ADD) || !$guild_id) return false;
		
		//Load up the applications.
		$this->db->select('users.username, applications.id, applications.date, applications.message');
		$this->db->join('users', 'users.id = applications.uid', 'inner');
		$this->db->where('applications.gid', $guild_id);
		$rs = $this->db->get('applications');
		
		//Make sure they exist.
		if($rs->num_rows < 1) return false;
		
		$data = array();
		foreach($rs->result() as $row) $data[] = $row;
		return $data;
	}
	
	public function get_members($sort, $dropdown = false)
	{
		//The user permission is passed through the controller.
		$guild_id = $this->user->guild;
		if(!$guild_id) return false;
		
		//If we want to get the member list for a dropdown.
		if($dropdown) {
			$data = array(0 => '--');
			$rs = $this->db->select('id, username')->get_where('users', array('guild' => $guild_id));
			
			//Empty result set.
			if($rs->num_rows() < 1) return $data;
			
			//Push to array if it is not the person that looked it up.
			foreach($rs->result() as $row) if($row->id != $this->user->id) $data[$row->id] = $row->username;
			return $data;
		}
		
		//Load up the applications.
		$this->db->select('id, username, rank, joined')->order_by('rank', 'desc');
		$rs = $this->db->get_where('users', array('guild' => $guild_id));
		
		//Make sure they exist.
		if($rs->num_rows < 1) return false;
		
		$data = array();
		foreach($rs->result() as $row) {
			switch(true) {
				case $row->rank == PERMISSION_GUILD_MEMBER: $row->rank = 'Member';break;
				case $row->rank == PERMISSION_GUILD_OFFICER: $row->rank = 'Officer';break;
				case $row->rank == PERMISSION_GUILD_LEADER: $row->rank = 'Leader';break;
			}
			$data[] = $row;
		}
		return $data;
	}
	
	public function get_permissions()
	{
		//Are we even logged in?
		if(!$this->user->id) return false;

		//Since we know the user exists we can go directly to the final step after getting the row.
		$this->db->select('rank')->from('users')->where('id', $this->user->id);
		return $this->db->get()->row()->rank;
	}

	public function get_logs()
	{
		//Not in a guild? Don't bother!
		$guild_id = $this->user->guild;
		if($guild_id == 0) return false;
		
		$this->db->select('id, processed, hash, notes, private, raid_date')->where('gid', $guild_id)->order_by('uploaded', 'desc');
		$query = $this->db->get('logs');
		if($query->num_rows() > 0) {
			$results = array();
			foreach($query->result() as $row) $results[] = $row;
			$query->free_result();
			return $results;
		}
		return false;
	}

	public function get_shards()
	{
		//Load up the shards ordered by region then ordered by name.
		$this->db->order_by('region', 'asc')->order_by('name', 'asc');
		$q = $this->db->get('shards');
		//Push the data into an object array.
		$data = array();
		foreach($q->result() as $row) $data[$row->id] = $row->region.'-'.$row->name;
		return $data;
	}
	
	public function edit_logs($data)
	{
		//Make sure some phony data isn't being submitted to prevent errors.
		if(!isset($data['logs']) || (empty($data['logs']))) return false;

		//Iterate through each log update and set it in the database.
		foreach($data['logs'] as $id => &$log_settings) {
			//Make the update field.
			$update = array(
				'private' => isset($log_settings['public']) ? 0 : 1,
				'notes' => $log_settings['notes'],
			);
			//If the date is bullshit skip it.
			if(($timestamp = strtotime($log_settings['date'])) !== false) $update['raid_date'] = date('Y-m-d', $timestamp);
			
			//Finally execute the update.
			$this->db->where(array(
				'id' => $id,
				'gid' => $this->user->guild
			))->update('logs', $update);
		}
		return true;
	}
	
	public function delete_log($data)
	{
		//Check for valid data.
		if(!isset($data['id']) || empty($data['id']) || !$this->user->has_permission(ACCESS_LOG_REMOVE)) return false;

		//See if this is a valid delete statement.
		$rs = $this->db->get_where('logs', array(
			'gid' => $this->user->guild,
			'id' => $data['id']
		));
		
		//If valid, commence delete.
		if($rs->num_rows()) {
			$vars = $rs->row();
			$this->db->delete('logs', array(
				'gid' => $vars->gid,
				'id' => $vars->id
			));
			@unlink(DIR_LOGS.$vars->hash.'.gz');
			return true;
		}
		return false;
	}
	
	public function remove_user($data)
	{
		//Make sure the data we need is set.
		if(!isset($data['uid']) || empty($data['uid']) || !$this->user->has_permission(ACCESS_GUILD_REMOVE)) return false;
		
		//Now let's kick the requested user from the guild.
		$this->db->where(array(
			'id' => $data['uid'],
			'guild' => $this->user->guild
		))->update('users', array(
			'guild' => 0,
			'rank' => 0
		));
		
		//Return the final result.
		return $this->db->affected_rows() > 0;
	}
	
	public function add_guild($data)
	{
		//Check that the necessary data is set.
		if(!isset($data['guildname'], $data['shard'])) {
			$this->error = 'An error occurred while trying to add your guild.';
			return false;
		}

		//Check for length.
		if(strlen($data['guildname']) < 2) {
			$this->error = 'The guild name you have entered is too short.';
			return false;
		}

		//Verify the guild name does not exist.
		$this->db->from('guilds')->where(array(
			'LOWER(`name`)' => strtolower($data['guildname']),
			'shard' => $data['shard']
		));
		if($this->db->count_all_results()) {
			$this->error = 'The guild name you have picked is already on this shard.';
			return false;
		}

		//If it does not exists, add it.
		$this->db->insert('guilds', array(
			'name' => $data['guildname'],
			'shard' => $data['shard']
		));
		//Also update the user's guild status.
		$gid = $this->db->insert_id();
		$this->db->update('users', array(
			'guild' => $gid,
			'rank' => PERMISSION_GUILD_LEADER,
			'joined' => date("Y-m-d")
		), array(
			'id' => $this->user->id
		));
		
		//Lastly we need to set the guild ID.
		$this->user->guild = $gid;
		$this->user->update_rank(PERMISSION_GUILD_LEADER, false);
		return true;
	}
	
	public function edit_settings($data)
	{
		//Going into this function assuming everything is set correctly.
		$errors = array();
		$changes_made = false;
		
		//First check and see if the user wanted to change their password.
		if(!empty($data['old_password']) || !empty($data['new_password1']) || !empty($data['new_password2'])) {
			//Load up the password helper.
			$this->load->helper('password');
			
			//For efficiency check and see if the new passwords match and have 6 characters.
			if($data['new_password1'] != $data['new_password2']) {
				$errors[] = 'The passwords provided do not match.';
			}
			if(strlen($data['new_password1']) < 6) {
				$errors[] = 'The passwords provided are too short.';
			}
			
			//All of the criteria met, update the password.
			if(empty($errors)) {
				$this->db->where(array(
					'id' => $this->user->id,
					'password' => gen_password($data['old_password'])
				))->update('users', array(
					'password' => gen_password($data['new_password1'])
				));
				//This means the old password is the same as the new one or the old password was wrong.
				if($this->db->affected_rows() < 1) {
					$errors[] = 'The old password provided does not match.';
				}
			}
			
			$changes_made = true;
		}
		
		//Check if changes were made.
		if(!$changes_made) {
			$errors[] = 'No settings were changed.';
		}
		
		if(empty($errors)) return true;
		$this->error = implode('<br />', $errors);
		return false;
	}
	
	public function load_guilds($data)
	{
		//Make sure we have a valid ID.
		if(!isset($data['id']) || !is_numeric($data['id'])) return false;
		
		//Prepare out output object.
		$out = array(
			'guilds' => array()
		);
		
		//Load up guilds.
		$rs = $this->db->select('id, name')->where('shard', $data['id'])->order_by('name', 'desc')->get('guilds');
		if($rs->num_rows() < 1) {
			$out['error'] = "There are no guilds on the shard you have selected.";
		} else {
			//Make a blank guild.
			$row = new stdClass;
			$row->id = 0;
			$row->name = '--';
			$out['guilds'][] = $row;
			
			//Output the rest of the guilds.
			foreach($rs->result() as $row) $out['guilds'][] = $row;
		}
		$rs->free_result();
		
		$this->output->set_output(json_encode($out));
	}
	
	public function get_error()
	{
		return $this->error;
	}
}
?>