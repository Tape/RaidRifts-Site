<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report_model extends CI_Model
{
	public function getGuildName($id)
	{
		$query = $this->db->select('name')->get_where('guilds', array('id' => $id));
		if($query->num_rows() <= 0) return false;
		return $query->row()->name;
	}
}
?>