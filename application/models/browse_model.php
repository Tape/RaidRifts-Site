<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(DIR_CLASS."logparser.class.php");

class Browse_model extends CI_Model
{
	public function get_shards($region = '')
	{
		//Prepare data.
		$data = array();
		$rs = null;
		
		//Create the query.
		$this->db->order_by('region', 'asc')->order_by('name', 'asc');
		if(empty($region))
			$rs = $this->db->get('shards');
		else
			$rs = $this->db->get_where('shards', array('region' => $region));
		
		//Check the data.
		if(is_null($rs) || $rs->num_rows() < 1) return false;
		//Build array.
		foreach($rs->result() as $row) $data[] = $row;
		//Free result.
		$rs->free_result();
		
		//Return data.
		return $data;
	}
	
	public function get_shard($id = null)
	{
		//Do we have an id?
		if(is_null($id)) return false;
		
		//Load up the shard.
		if(($data = $this->get_shard_name($id)) === false) return false;
		
		//Prepare to load up guilds.
		$rs = $this->db->get_where('guilds', array('shard' => $id));
		
		//Return false to show there are no guilds here.
		if($rs->num_rows() < 1) return $data;
		foreach($rs->result() as $row) $data->guilds[] = $row;
		$rs->free_result();
		return $data;
	}
	
	private function get_shard_name($id = null)
	{
		//Do we have an id?
		if(is_null($id)) return false;
		
		//Load up the shard name.
		$rs = $this->db->get_where('shards', array('id' => $id));
		
		//Check if this is valid and then return it.
		if($rs->num_rows < 1) return false;
		$data = $rs->row();
		$rs->free_result();
		return $data;
	}
	
	public function get_guild($id = null, $year = null, $month = null)
	{
		//Make sure all necessary parts are set.
		if(is_null($id) || is_null($year) || is_null($month)) return false;
		
		//Check for the guild.
		$rs = $this->db->get_where('guilds', array('id' => $id));
		if($rs->num_rows() < 1) return false;
		$data = $rs->row();
		$rs->free_result();
		
		//If we have gotten here the shard exists.
		$data->shard = $this->get_shard_name($data->shard);
		$data->calendar = array();
		
		//Prepare the where statement.
		$where = array(
			'gid' => $id,
			'YEAR(`raid_date`)' => $year,
			'MONTH(`raid_date`)' => $month
		);
		
		//We want to show logs the user has permission to view.
		if($this->user->id === false || $this->user->guild != $id) $where['private'] = 0;
		
		//Load results.
		$this->db->select('hash, DAY(`raid_date`) AS day, raid_date, notes')->where($where)->order_by('raid_date', 'asc');
		$rs = $this->db->get('logs');
		
		//Don't care about empty sets!
		if($rs->num_rows() < 1) return $data;
		
		//Develop the calendar stuff.
		$tmp = array();
		foreach($rs->result() as $row) {
			$out = '<a href="'.base_url().'report/view/'.$row->hash.'/">';
			$out .= date('m/j/Y', strtotime($row->raid_date));
			$out .= '<span>'.(empty($row->notes) ? 'No notes.' : $row->notes).'</span>';
			$out .= '</a>';
			$tmp[$row->day]['rows'][] = $out;
		}
		$rs->free_result();
		
		//Now push the calendar data to our data.
		foreach($tmp as $day => &$row) $data->calendar[$day] = implode("\n", $row['rows']);
		unset($tmp);
		return $data;
	}
}
?>