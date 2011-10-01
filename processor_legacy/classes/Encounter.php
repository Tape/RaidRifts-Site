<?php
class Encounter
{
	//Private variables.
	private $actors_names = array();
	private $actors_status = array();
	private $list_players = array();
	private $list_enemies = array();
	
	private $start_time;
	private $start_line;
	private $end_time;
	private $end_line;
	
	private $id;
	private $cached = false;
	
	private $ratio;
	private $boss_id;
	private $killed_bosses;
	
	private $prev;
	
	//Public variables.
	public $last_active;
	public $last_line;
	public $multi = false;
	
	public function Encounter($timestamp, $start_line, &$previous, &$killed_bosses)
	{
		$this->start_time = $timestamp;
		$this->start_line = $start_line;
		$this->id = mt_rand();
		$this->killed_bosses = $killed_bosses;
		$this->prev = $previous;
	}
	
	public function actor(&$parser)
	{
		if(!isset($this->actors_names[$parser->origin_id])) {
			//Killed bosses need to be skipped!
			if(in_array($parser->origin_id, $this->killed_bosses)) return false;
			
			//Check if this is a multi-phase boss.
			$i = 0;
			$encounter =& $this->prev;
			while($encounter != null) {
				//We don't want to go too far back.
				if($i >= 5) {
					unset($encounter);
					$encounter = null;
					break;
				}
				
				//Checking... does the boss id match? Also was the encounter a probable wipe?
				$boss_id = $encounter->get_boss();
				if($boss_id !== false && $boss_id == $parser->origin_id && $encounter->player_death_ratio() < .75) {
					//If we have a match this is multi-phase. Reset the start of the fight.
					$this->start_time = $encounter->start_time;
					$this->start_line = $encounter->start_line;
					$this->multi = true;
					break;
				}
				$encounter =& $encounter->prev;
				$i++;
			}
			
			//Add this new unit to our actor array.
			$this->actors_names[$parser->origin_id] = $parser->origin_name;
			$this->actors_status[$parser->origin_id] = true;
			
			//Friendly variables. R = Raid, G = Group, C = Character.
			$friendly = in_array($parser->origin_var1, array('R', 'G', 'C'));
			//Establish a list of players if they are friendly and not NPCs.
			if($friendly && $parser->origin_type != 'N') $this->list_players[] = $parser->origin_id;
			//NPC's excluding pets, therefore all enemies.
			elseif(!$friendly) $this->list_enemies[] = $parser->origin_id;
			
			//Force a cache reset.
			$this->cached = false;
			return true;
		}
		return $this->actors_status[$parser->origin_id];
	}
	
	public function kill($id)
	{
		$this->actors_status[$id] = false;
		//Force a cache reset.
		$this->cached = false;
	}
	
	public function end_combat()
	{
		$this->end_time = $this->last_active;
		$this->end_line = $this->last_line;
	}
	
	public function get_boss()
	{
		$this->cache();
		return $this->boss_id;
	}
	
	public function get_length()
	{
		return $this->end_time - $this->start_time;
	}
	
	public function get_name($id)
	{
		return $this->actors_names[$id];
	}
	
	public function is_alive($id)
	{
		return $this->actors_status[$id];
	}
	
	public function get_line($start)
	{
		if($start) return $this->start_line;
		return $this->end_line;
	}
	
	public function player_death_ratio()
	{
		$this->cache();
		return $this->ratio;
	}
	
	private function cache()
	{
		//Don't renew the cache on stuff already done.
		if($this->cached === true) return;
		
		//Cache the player death ratio.
		$count = 0;
		$dead = 0;
		foreach($this->list_players as $id) {
			$count++;
			if($this->actors_status[$id] === false) $dead++;
		}
		
		if($count > 0) $this->ratio = $dead/$count;
		else $this->ratio = 1;
		
		//Cache the boss ID.
		$this->boss_id = false;
		foreach($this->list_enemies as &$id) if(in_array($this->actors_names[$id], Bosses::$list)) {
			$this->boss_id = $id;
			break;
		}
		
		//Let the class know it is now cached.
		$this->cached = true;
	}
	
	public function get_id()
	{
		return $this->id;
	}
}
?>