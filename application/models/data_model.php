<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(DIR_CLASS."logparser.class.php");

class Data_model extends CI_Model
{
	private $data = null;
	private $parser = null;
	private $actions = null;
	private $graphdata = null;
	private $players = null;
	private $is_dying = false;
	
	private $global_filter = array(
		1133456236,//	Sacrifice Life: Damage - Warlock life tap
	);
	private $heal_filter = array(
		646325348,//	Combat res
		759111971,//	Combat res
		1256404592,//	Combat res
		1297021479,//	Combat res
		216918080//		Matron's ichor shit.
	);
	private $damage_filter = array(
		318277069,//	Deathly Flames - Greenscale
		29203840,//		Shocking Cipher - Plutonus the Immortal
		75503143,//		Sourcestone Annihilation - Alsbeth the Discordant
		680238487,//	Essence Transfer - Warmaster Galenir
		785553181,//	Ricocheted Obliteration Beam - Inquisitor Garau
		1920920671//	Forked Blast - Rune King Molinar
	);
	private $boss_list = array();
	
	public function __construct()
	{
		parent::__construct();
		$rs = $this->db->order_by('id', 'asc')->get('bosses');
		foreach($rs->result() as $row) $this->boss_list[] = $row;
		$rs->free_result();
	}
	
	/**
	 *
	 */
	public function getData($log_id, $lower, $upper)
	{
		//Return data if it is already loaded.
		if($this->data != null) return $this->data;

		//Initialize stuff.
		$data =& $this->data;
		$parser =& $this->parser;
		$graph_data =& $this->graphdata;
		$players =& $this->players;
		//Variables needed for graph stuff.
		$elapsed = 0;

		//Loop through the contents and prepare data.
		$players = array();
		$parser = new LogParser($log_id, $lower, $upper, $this->db);
		$data = new stdClass;
		$data->maximum = array('dmg' => 0, 'heal' => 0, 'taken' => 0);
		$data->markings = array();
		
		//Fetch encounter data from database.
		$rs = $this->db->where('id_log', $parser->log_id)
			->where('`start` <=', $lower)
			->where('`end` >=', $upper)->get('attempts');
		$row = $rs->row();
		$rs->free_result();
		$data->encounter_boss = $this->boss_list[$row->id_boss-1]->EN;
		$data->wipe = $row->wipe;
		
		//Buffers for markings.
		$raid_buffer = array();
		$life_buffer = array();
		
		while($status = $parser->parseLine()) {
			//Skip misread lines.
			if($status != LogParser::SUCCESS) continue;
			//Timestamp!
			$timestamp = $parser->getSeconds();
			//Set the initial value for the timer if it hasn't started.
			if(!isset($data->l)) {
				$data->l = $timestamp;
				$data->timestamp = $timestamp;
			}

			//Ignore NPCs but see if we have detected which one it is.
			$is_npc = $parser->isNPC();
			$is_pet = $parser->isPet();

			//Ignore energy/mana gains or abilities we want filtered out.
			if($parser->type_id == 27 || in_array($parser->attack_id, $this->global_filter)) continue;
			
			//Set initial values.
			$id = $is_pet ? $parser->pet_owner_id : ($is_npc ? $parser->target_id : $parser->origin_id);
			
			//Determine where we want to store the abilities.
			$loc = false;
			switch($parser->type_id) {
			case 3: case 4: case 23: $loc = $is_npc ? ($parser->target_type == 'P' ? 'dmg' : false) : 'dmg';break;//Damage
			case 5: case 28: //Healing
				$loc = false;
				switch(true) {
				case in_array($parser->attack_id, array(646325348, 759111971, 1256404592, 1297021479)):
					$life_buffer[] = array(
						'type' => 1,
						'time' => $timestamp - $data->l,
						'length' => 0,
						'actor' => $parser->target_name,
						'origin' => $parser->origin_name,
						'color' => '#0B0'
					);
				case !in_array($parser->attack_id, $this->heal_filter):
					if(!$is_npc) $loc = 'heal';
					break;
				}
				break;
			case 6: case 7: //Attemting to capture bloodlust mechanics.
				//Capture only stuff that is cast on themself.
				$loc = false;
				if($parser->origin_id != $parser->target_id || !in_array($parser->attack_id, array(766317719, 1742325987, 1559448016))) break;
				
				//If this was when the buff was cast...
				if($parser->type_id == 6) {
					$raid_buffer[$parser->origin_id] = array(
						'type' => 2,
						'time' => $timestamp - $data->l,
						'length' => 0,
						'actor' => $parser->origin_name,
						'origin' => $parser->attack_name
					);
					
					//Assign a color for the 3 different raid cooldowns.
					$color =& $raid_buffer[$parser->origin_id]['color'];
					switch($parser->attack_id) {
						case 766317719: $color = '#004';break;
						case 1742325987: $color = '#005';break;
						case 1559448016: $color = '#006';break;
					}
				} else {
					$buffer =& $raid_buffer[$parser->origin_id];
					$buffer['length'] = $timestamp - $data->l - $buffer['time'];
					$data->markings[] = $buffer;
				}
				break;
			case 11: //Dying squirtle
				if($is_npc && $parser->target_type == 'P') $life_buffer[] = array(
					'type' => 0,
					'time' => $timestamp - $data->l,
					'length' => 0,
					'actor' => $parser->target_name,
					'origin' => $parser->origin_name,
					'color' => '#B00'
				);
				break;
			default: $loc = false;break;
			}

			//Filter out stuff we don't care about.
			if(!$loc) continue;
			//Also filter out stuff that does zero damage.
			if($parser->damage == 0 && $parser->absorbed == 0) continue;
			//Filtering complete, get the grand total for what we want.
			$total = $is_npc ? $parser->damage : $parser->damage + $parser->absorbed;

			//Now that we've filtered out the crap make sure this person exists.
			if(!isset($data->breakdown[$id])) {
				$data->breakdown[$id] = array(
					'dmg' => array(),
					'heal' => array(),
					'taken' => array(),
					'totals' => array('dmg' => 0, 'heal' => 0, 'taken' => 0),
					'activity' => array(
						'dmg' => array('total' => 0, 'last' => $timestamp, 'start' => $timestamp),
						'heal' => array('total' => 0, 'last' => $timestamp, 'start' => $timestamp),
						'taken' => array('total' => 0, 'last' => $timestamp, 'start' => $timestamp),
					)
				);
			}
			//Make sure the name is set as well.
			if(!$is_pet && !isset($data->breakdown[$id]['name'])) {
				$data->breakdown[$id]['name'] = !$is_npc ? $parser->origin_name : $parser->target_name;
			}

			//Store the ability cast.
			if($is_pet) {
				if(!isset($data->breakdown[$id]['pet'])) {
					$data->breakdown[$id]['pet'] = array(
						'name' => $parser->origin_name,
						'totals' => array('dmg' => 0, 'heal' => 0)
					);
				}
				$this->addValue($data->breakdown[$id]['pet'][$loc][$parser->attack_name], $total);
				$this->addValue($data->breakdown[$id]['pet']['totals'][$loc], $total);
			} else {
				//Filter and fix unwanted results.
				if($is_npc && $loc == 'dmg') $loc = 'taken';
				elseif($is_npc) continue;

				//Finally output them.
				$this->addValue($data->breakdown[$id][$loc][$parser->attack_name], $total);
			}

			//Add it to the player total.
			$data->breakdown[$id]['totals'][$loc] += $total;
			//Add it to the graph data.
			$this->addValue($graph_data[$elapsed][$loc][$id], $total);

			//Maximum dmg/heal by user.
			if($data->maximum[$loc] < $data->breakdown[$id]['totals'][$loc]) {
				$data->maximum[$loc] = $data->breakdown[$id]['totals'][$loc];
			}
			//Add the ability to the grand total.
			$this->addValue($data->total[$loc], $total);
			//Update actor's activity.
			$data->breakdown[$id]['activity'][$loc]['last'] = $timestamp;

			//Calculate stuff every second.
			if($timestamp - $data->timestamp >= 1) {
				//Activity.
				foreach($data->breakdown as &$actor) {
					foreach($actor['activity'] as $type => &$arr)
					if($timestamp - $arr['last'] <= 3 && $arr['start'] != $arr['last']) {
						$arr['total']++;
					}
				}
				//Put zeroes in empty graph data slots.
				foreach($graph_data[$elapsed] as $type => &$ids) foreach($data->breakdown as $id => &$arr) {
					if(!isset($ids[$id])) $ids[$id] = 0;
				}

				//Update the elapsed timer for graph data.
				$elapsed += $timestamp - $data->timestamp;
				//Reset the timestamp.
				$data->timestamp = $timestamp;
			}
		}
		
		//Push deaths/revives to the markings array.
		foreach($life_buffer as &$mark) $data->markings[] = $mark;

		//Calculate the final length.
		$data->l = $parser->getSeconds() - $data->l;
		$this->parseGraphData($graph_data);
		return $data;
	}

	/**
	 *
	 */
	function getUserData($log_id, $lower, $upper, $name)
	{
		//Return data if it is already loaded.
		if(isset($this->data)) return $this->data;

		//Initialize stuff.
		$data =& $this->data;
		$parser =& $this->parser;
		
		//Init vars.
		$parser = new LogParser($log_id, $lower, $upper, $this->db);
		$user_id = $parser->getID($name);
		
		//Start up the data class.
		$data = new stdClass;
		$data->abilities = array('dmg' => array(), 'heal' => array(), 'taken' => array());
		$data->deaths = array();
		$data->totals = array('dmg' => 0, 'heal' => 0, 'taken' => 0, 'heal_modified' => 0);
		
		//Loop through the contents and prepare data.
		while($status = $parser->parseLine()) {
			//Skip misread lines.
			if($status != LogParser::SUCCESS) continue;
			//Load up a timestamp.
			$timestamp = $parser->getSeconds();
			//Set the initial value for the timer if it hasn't started.
			if(!isset($data->l)) {
				$data->l = $timestamp;
				$data->timestamp = $timestamp;
			}

			//Check if this is even the person we want.
			if(($parser->origin_id != $user_id && $parser->target_id != $user_id) || in_array($parser->attack_id, $this->global_filter)) continue;

			//Capture the data types.
			switch($parser->type_id) {
			//Damage |3=hit,4=suffers,10=miss,15=dodge,16=parry,19=resist,23=crit|
			case 3: case 4: case 10: case 15: case 16: case 19: case 23:
				//Check if this is a player.
				$is_player = $parser->origin_id == $user_id;
				if($is_player) $attack =& $data->abilities['dmg'][$parser->attack_name];
				else $attack =& $data->abilities['taken'][$parser->attack_name];
				
				//Set the variable if necessary.
				if(!isset($attack)) {
					$attack = array(
						'total' => 0,
						'cast' => 0,
						'max' => 0,
						'crits' => 0,
						'misses' => 0,
						'id' => $parser->attack_id
					);
					//If this isn't a player we want to add more accurate details.
					if(!$is_player) {
						$attack += array(
							'parry' => 0,
							'dodge' => 0,
							'origin' => $parser->origin_name,
							'block_count' => 0,
							'block_total' => 0
						);
					}
				}
				$total = $is_player ? $parser->damage + $parser->absorbed : $parser->damage;
				$attack['total'] += $total;
				$attack['cast'] += 1;
				$attack['max'] = $attack['max'] > $total ? $attack['max'] : $total;
				
				//Calculating minimum.
				if(isset($attack['min'])) {
					$attack['min'] = $attack['min'] < $total ? $attack['min'] : $total;
				} else $attack['min'] = $total;
				
				$attack['crits'] += $parser->type_id == 23 ? 1 : 0;
				$attack['misses'] += in_array($parser->type_id, array(10,15,16,19)) ? 1 : 0;
				//Log parry and dodges.
				if(!$is_player) switch($parser->type_id) {
					case 15:$attack['dodge']++;break;
					case 16:$attack['parry']++;break;
				}
				//Log some block details.
				if(!$is_player && $parser->blocked > 0) {
					$attack['block_count']++;
					$attack['block_total'] += $parser->blocked;
				}
				$data->totals[$is_player ? 'dmg' : 'taken'] += $total;
				
				if(!$is_player) {
					//Logging a hostile action against the player.
					$this->logAction($parser->type_id == 23 ? 'crits' : $parser->type_id == 3 ? 'hits' : 'damages');
					//We want to find the deathblow if the character is going to die.
					if($parser->overkill > 0 || $this->is_dying) {
						$this->processDeath();
					}
				}
				break;
			//Healing |5=heal,28=crit|
			case 5: case 28:
				//Dave: For some reason disarm attacks appear in here so there's an extra statement in the if.
				if($parser->origin_id == $user_id && ($parser->overheal != 0 || $parser->damage != 0)) {
					//Filter out stuff we don't want.
					if(in_array($parser->attack_id, $this->heal_filter)) continue;
					
					$heal =& $data->abilities['heal'][$parser->attack_name];
					if(!isset($heal)) {
						$heal = array(
							'total' => 0,
							'total_modified' => 0,
							'cast' => 0,
							'max' => 0,
							'min' => 0,
							'crits' => 0,
							'overheal' => 0,
							'id' => $parser->attack_id
						);
					}
					$heal['total'] += $parser->damage;
					$heal['total_modified'] += $parser->damage + $parser->overheal;
					$heal['cast'] += 1;
					$heal['max'] = $heal['max'] > $parser->damage ? $heal['max'] : $parser->damage;
					$heal['min'] = isset($heal['min']) ? $heal['min'] < $parser->damage ? $heal['min'] : $parser->damage : $parser->damage;
					$heal['crits'] += $parser->type_id == 28 ? 1 : 0;
					$heal['overheal'] += $parser->overheal;
					$data->totals['heal'] += $parser->damage;
					$data->totals['heal_modified'] += $parser->damage + $parser->overheal;
				} else {
					//Logging an incoming heal to the player.
					$this->logAction('heals');
				}
				break;
			//Deaths |11=slain,12=died|
			case 11: case 12:
				if(($parser->type_id == 11 && $parser->origin_id != $user_id) || $parser->type_id == 12) {
					$this->is_dying = true;
				}
				break;
			}
		}

		//Calculate the final length.
		$data->l = $parser->getSeconds() - $data->l;
		
		//Uset unneccesary data and sort.
		unset($this->parser, $this->actions);
		uasort($data->abilities['dmg'], array('Data_model', 'sort'));
		uasort($data->abilities['heal'], array('Data_model', 'sort'));
		uasort($data->abilities['taken'], array('Data_model', 'sort'));
		return $data;
	}

	/**
	 *
	 */
	private function parseGraphData( &$data )
	{
		//Set up a bunch of our variables.
		$tmp = array();
		$data_size = $this->data->l;
		$k = 0;
		$const = $data_size/179;

		//Loop through each second.
		for($i = 0; $i <= $data_size; $i++) {
			if($data_size > 180 && $i != ceil($const * $k) && $i != $data_size) continue;
			$k++;
			//Now we want to find a 3 second average. First lets grab values.
			for($j = -2; $j <= 2; $j++) {
				//Edge testing.
				if($i + $j < 0 || $i + $j >= $data_size) continue;
				
				foreach($this->data->breakdown as $id => $ignore) foreach(array('dmg', 'heal', 'taken') as $type) {
					//Don't care about people who didn't contribute to the data set.
					if($ignore['totals'][$type] > 0)
					
					$row =& $data[$i + $j][$type];
					//Make sure to add zeroes for no activity...
					if(!isset($row) || !isset($row[$id])) {
						$this->addValue($tmp[$i][$type][$id], 0);
						$this->addValue($tmp[$i][$type]['total'], 0);
					} else {
						$this->addValue($tmp[$i][$type][$id], $row[$id]);
						$this->addValue($tmp[$i][$type]['total'], $row[$id]);
					}
				}
			}
			
			foreach($tmp[$i] as $type => &$ids) foreach($ids as $id => &$val) {
				if($id == 'total') {
					$this->data->graph[$type][$i] = $val / 5;
					continue;
				}
				$this->data->breakdown[$id]['graph'][$type][$i] = $val / 5;
			}
		}

		//Finally we can cleanup and return.
		unset($data);
		unset($tmp);
		return true;
	}

	/**
	 *
	 */
	private function logAction($action)
	{
		$parser =& $this->parser;
		if($parser->damage == 0) return true;
		
		$this->actions[] = array(
			$parser->getSeconds(),
			$parser->origin_name,
			$parser->attack_name,
			$action,
			$parser->target_name,
			$parser->damage*($action == 'heals' ? -1 : 1),
			$action == 'heals' ? $parser->overheal : $parser->overkill
		);
		
		if(count($this->actions) > 20) {
			array_splice($this->actions, 0, 1);
		}
		//var_dump(count($this->actions));
		
		return true;
	}

	/**
	 *
	 */
	private function processDeath() {
		//Get the final timestamp value.
		$timestamp = $this->actions[sizeof($this->actions)-1][0];
		$health = 0;
		
		//Reverse the order to calculate current health.
		krsort($this->actions);
		
		$tmp = array();
		//Process the death and add a timestamp.
		foreach($this->actions as &$action) {
			//Skip any action over 10 seconds.
			$deficit = $action[0] - $timestamp;
			if($deficit < -10) continue;
			
			//Bug with overkill, remove it from the action value.
			if($action[3] != 'heals') $action[5] -= $action[6];
			
			//Calculate current health.
			$health += $action[5];
			
			//Build up the phrase.
			$phrase = sprintf("%s's %s %s %s for %d", $action[1], $action[2], $action[3], $action[4], abs($action[5]));
			//Detect if there's overkill.
			if($action[6] > 0) $phrase .= sprintf(' (%d %s).', $action[6], $action[3] == 'heals' ? 'overheal' : 'overkill');
			else $phrase .= '.';
			
			//Build the health phrase.
			if($action[5] < 0) $health_phrase = '<span class="green">'.$health.'</span>';
			elseif($action[5] > 0) $health_phrase = '<span class="red">'.$health.'</span>';
			else $health_phrase = $health;
			
			//Log the action.
			$tmp[] = array($deficit, $health_phrase, $phrase);
		}
		
		//Reverse the order again to go back to normal.
		krsort($tmp);

		//Reset the variables.
		$this->data->deaths[] = $tmp;
		$this->actions = array();
		$this->is_dying = false;
	}

	/**
	 *
	 */
	private static function sort($a, $b)
	{
		if($a['total'] == $b['total']) return 0;
		return $a['total'] > $b['total'] ? -1 : 1;
	}

	/**
	 *
	 */
	private function addValue(&$dest, $value, $initial = 0)
	{
		if(!isset($dest)) {
			$dest = $initial;
		}
		$dest += $value;
		return true;
	}

	function get_sidebar_data($log_id)
	{
		//Pull the data from the database.
		$this->db->select('logs.id, guilds.name, logs.private, logs.gid, logs.raid_date');
		$this->db->join('guilds', 'guilds.id = logs.gid', 'inner');
		$rs = $this->db->get_where('logs', array('hash' => $log_id));
		if($rs->num_rows() < 1) return false;
		$data = $rs->row();
		$rs->free_result();
		
		//Grab relevant boss and attempt data.
		$this->db->select('bosses.EN as name, attempts.start, attempts.end, attempts.length, attempts.wipe');
		$this->db->join('bosses', 'bosses.id = attempts.id_boss', 'inner');
		$query = $this->db->get_where('attempts', array('id_log' => $data->id));
		//Return basic data in case there were no attempts logged.
		if($query->num_rows() < 1) return $data;
		
		//Prepare data.
		$data->attempts = array();
		foreach($query->result() as $attempt) $data->attempts[$attempt->name][] = $attempt;
		return $data;
	}
}
?>