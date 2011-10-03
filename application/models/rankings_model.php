<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(DIR_CLASS."logparser.class.php");

class Rankings_model extends CI_Model
{
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
		$this->db->select('bosses.EN AS name, COUNT(attempts.id) AS attempts, bosses.id')->join('attempts', 'attempts.id_boss = bosses.id', 'inner')->group_by('bosses.id');
		$rs = $this->db->get_where('bosses', array(
			'bosses.id' => $id_boss
		));
		if($rs->num_rows() < 1) return false;
		$data = $rs->row();
		$rs->free_result();
		$data->encounters = array();
		$data->offset = $offset;
		
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