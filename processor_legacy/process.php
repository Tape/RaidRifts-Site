<?php
include('config.php');
include('classes'.DS.'Encounter.php');
include('classes'.DS.'Bosses.php');

include($include_path.'classes'.DS.'logparser.class.php');
$filepath = $include_path.'combat_logs'.DS.$argv[1].".gz";

//Connect to mysql.
mysql_connect($mysql_host, $mysql_user, $mysql_pass);
mysql_select_db($mysql_db);

//Create the parser.
$parser = new LogParser($argv[1], 1, -1);

//Combat factors.
$v = new stdClass;
$v->combat = false;
$v->day = 1;
$v->date = 0;
$v->timestamp = 0;
$v->ts = null;
$v->ts_last = -1;
$v->combat_actions = array(2, 3, 4, 10, 15, 16, 19, 22, 23, 26);

//Log and line stuff.
$v->line = 0;
$v->byte_count = 0;
$v->killed_bosses = array();
$params = array();
$encounter = null;

//Now we need to parse it.
while($parser->parseLine(true)) {
	$v->line++;
	$v->byte_count += $parser->getBytes();
	if($v->line%100000 == 0) $params['milestone'][] = $v->byte_count;
	
	//Check if time rolled over.
	$v->ts = $parser->getSeconds();
	if($v->ts < $v->ts_last) $v->date += 86400;
	$v->timestamp = $v->date + ($v->ts_last = $v->ts);
	
	//We want to see if this is a permitted action to start combat.
	$is_action = in_array($parser->type_id, $v->combat_actions);
	
	//Starting condition to get us in combat.
	if(!$v->combat && $is_action) {
		$v->combat = true;
		$encounter = new Encounter($v->timestamp, $v->line, $encounter, $v->killed_bosses);
	}
	
	//Handle mid-combat events.
	if($v->combat) {
		if($is_action && $encounter->actor($parser)) {
			$encounter->last_active = $v->timestamp;
			$encounter->last_line = $v->line;
			continue;
		}
		
		//Somebody died!
		if($parser->type_id == 11) {
			$encounter->kill($parser->target_id);
		} elseif($parser->type_id == 12) {
			$encounter->kill($parser->origin_id);
		}
		
		//Hostile action didn't happen, check for expiration of combat.
		if($v->timestamp - $encounter->last_active >= 6) {
			$boss_id = $encounter->get_boss();
			
			//We have met the condition to end combat.
			$encounter->end_combat();
			//Check if we have a boss of importance.
			if($boss_id !== false) {
				//Is this boss even alive?
				$alive = $encounter->is_alive($boss_id);
				//Snag his name.
				$boss_name = $encounter->get_name($boss_id);
				$boss_data = array(
					's' => $encounter->get_line(true),
					'e' => $encounter->get_line(false),
					'l' => $encounter->get_length(),
					'w' => $alive
				);
				
				//Add this to the killed bosses array.
				if(!$alive) $v->killed_bosses[] = $boss_id;
				
				//Last step... see if we have the minimum length.
				if($boss_data['l'] >= 30) {
					if($encounter->multi) array_pop($params['bosses'][$boss_name]);
					$params['bosses'][$boss_name][] = $boss_data;
				}
			}
			$v->combat = false;
		}
	}
}

//print_r($params);
$json = mysql_real_escape_string(json_encode($params));
mysql_query("UPDATE `logs` SET `processed` = ".(isset($params['bosses']) ? 1 : 3).", `vars` = '".$json."' WHERE `hash` = '".$argv[1]."' LIMIT 1");
mysql_close();
?>
