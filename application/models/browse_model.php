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
	
	public function get_guilds($id = null)
	{
		//Do we have an id?
		if(is_null($id)) return false;
		
		//Load up the guilds.
		$rs = $this->db->get_where('guilds', array('shard' => $id));
		
		//Return false to show there are no guilds here.
		if($rs->num_rows() < 1) return false;
		
		$data = array();
		foreach($rs->result() as $row) $data[] = $row;
		return $data;
	}
	
	public function get_logs($id = null, $year = null, $month = null)
	{
		//Make sure all necessary parts are set.
		if(is_null($id) || is_null($year) || is_null($month)) return false;
		
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
		if($rs->num_rows() < 1) return false;
		
		$tmp = array();
		foreach($rs->result() as $row) {
			if(!isset($tmp[$row->day])) {
				$tmp[$row->day] = array (
					'rows' => array()
				);
			}
			$out = '<a href="'.base_url().'report/view/'.$row->hash.'/">';
			$out .= date('m/j/Y', strtotime($row->raid_date));
			$out .= '<span>'.(empty($row->notes) ? 'No notes.' : $row->notes).'</span>';
			$out .= '</a>';
			$tmp[$row->day]['rows'][] = $out;
		}
		$data = array();
		foreach($tmp as $day => &$row) $data[$day] = implode("\n", $row['rows']);
		unset($tmp);
		return $data;
	}
	
	public function get_guild($id)
	{
		$rs = $this->db->select('name, faction')->where('id', $id)->get('guilds');
		if($rs->num_rows() < 1) return false;
		return $rs->row();
	}
	
	public function get_raids()
	{
		$this->db->select('bosses.id, bosses.EN AS boss_name, raids.EN AS raid_name, raids.size');
		$rs = $this->db->join('raids', 'raids.id = bosses.id_raid', 'inner')->get('bosses');
		if($rs->num_rows() < 1) return false;
		
		$data = array();
		foreach($rs->result() as $row) $data[$row->raid_name.' ('.$row->size.')'][] = $row;
		return $data;
	}
	
	public function get_attempts($id_boss, $offset)
	{
		//Need boss data first.
		$rs = $this->db->get_where('bosses', array(
			'id' => $id_boss
		));
		if($rs->num_rows() < 1) return false;
		$data = $rs->row();
		$rs->free_result();
		$data->encounters = array();
		
		//Need the encounter list now.
		$this->db->select('guilds.id, guilds.name, logs.hash, attempts.start, attempts.end, attempts.length, shards.name AS shard, shards.id AS shard_id, shards.region');
		$this->db->join('logs', 'logs.id = attempts.id_log', 'inner');
		$this->db->join('guilds', 'guilds.id = logs.gid', 'inner');
		$this->db->join('shards', 'shards.id = guilds.shard', 'inner');
		$rs = $this->db->where(array(
			'attempts.id_boss' => $id_boss,
			'attempts.wipe' => 0,
			'logs.private' => 0
		))->order_by('attempts.length', 'asc')->limit(100, 100*($offset-1))->get('attempts');
		if($rs->num_rows() < 1) return $data;
		
		//Store to array.
		foreach($rs->result() as $row) $data->encounters[] = $row;
		$rs->free_result();
		return $data;
	}
}
?>