<?php
class LogParser
{
	private $file_handle = null;

	const FILE_END = 0;
	const IGNORE_LINE = 1;
	const SUCCESS = 2;
	const MILESTONE = 100000;

	private $lower_limit = 0;
	private $upper_limit = 0;
	private $num_bytes = 0;
	private $is_forwarded = false;

	private $wordlist = array();
	private $byte_list = array();
	
	public $log_id;
	
	public $line = 0;
	public $hours;//		Hours
	public $minutes;//		Minutes
	public $seconds;//		Seconds

	public $type_id;//		The type id for the line.

	public $origin_type;//	Origin type (char)
	public $origin_var1;//	Origin ?
	public $origin_id;//	Origin ID

	public $target_type;//	Target type (char)
	public $target_var1;//	Target ?
	public $target_id;//	Target ID

	public $var1_var1;//	?
	public $var1_var2;//	?
	public $var1_var3;//	?

	public $pet_type;//		?
	public $pet_var1;//		?
	public $pet_owner_id;//	?

	public $origin_name;//	Origin's name.
	public $target_name;//	Target's name.
	public $attack_id;//	ID value of the attack the origin casted.
	public $damage;//		Damage value of the attack cast.
	public $attack_name;//	Name of the attack cast.

	public $overheal;//		This is to calculate overheal.
	public $intercepted;//	Intercepted damage (partial resist?)
	public $blocked;//		Blocked damage.
	public $absorbed;//		Absorbed damage.
	public $overkill;//		Overkill from a killing blow.

	public function __construct( $log_id = null, $lower = null, $upper = null, &$db = null )
	{
		if(is_null($log_id) || is_null($upper) || is_null($lower)) {
			show_error("Application fault: No combat log was supplied. Please contact an administrator of the site.", 500);
		}
		if(defined('DIR_LOGS')) {
			$path = DIR_LOGS.$log_id.".gz";
		} else {
			global $filepath;
			$path = $filepath;
		}
		if(!file_exists($path)) {
			if(function_exists('show_error')) {
				show_error("Application fault: The combat log specified was not found. Please contact an administrator of the site.", 500);
			}
		}

		//Set up variables.
		$this->lower_limit = $lower;
		$this->upper_limit = $upper;
		$this->file_handle = gzopen($path, defined('FOPEN_READ') ? FOPEN_READ : 'r');

		//File successfully opened; Grab the milestone markers.
		if($this->file_handle && !is_null($db)) {
			$rs = $db->get_where('logs', array('hash' => $log_id));
			$row = $rs->row();
			$rs->free_result();
			$this->log_id = $row->id;
			$this->byte_list = json_decode($row->vars);
		}

		//We need to establish the word list, which is stored in the first line.
		$vars = explode('|', gzgets($this->file_handle));
		foreach($vars as &$word) {
			$split = explode("=", $word);
			$this->wordlist[(int)$split[0]] = $split[1];
		}
		unset($vars);
	}

	/**
	 * function parse()
	 *
	 * Parses the line, and helps to determine 
	 */
	public function parseLine($bytes = false)
	{
		//Exit if we are out of bounds.
		if($this->line > $this->upper_limit && $this->upper_limit != -1) {
			gzclose($this->file_handle);
			return self::FILE_END;
		}

		//Fast forward through the file.
		if(!$this->is_forwarded) {
			if($this->lower_limit >= self::MILESTONE) {
				$num = floor($this->lower_limit/self::MILESTONE);
				gzseek($this->file_handle, gztell($this->file_handle) + $this->byte_list[$num-1]);
				$this->line = $num*self::MILESTONE;
			}
			$this->is_forwarded = true;
		}

		$line = '';
		//Loop until we are within the boundary. Catch a line regardless.
		do {
			$line = gzgets($this->file_handle);
			$this->line++;
		} while($this->line < $this->lower_limit);

		//Close if we have set an unlimited boundary.
		if($this->upper_limit == -1) if($line === false) {
			gzclose($this->file_handle);
			return self::FILE_END;
		}

		//Parameter passed checking if we want to count bytes.
		if($bytes) $this->num_bytes = strlen($line);
		//Skip empty lines.
		if(empty($line)) return self::IGNORE_LINE;

		//Parse time.
		$frags = explode(',', $line);
		$this->timestamp = $frags[0];
		$timestamp = explode(":", $frags[0]);
		$this->hours = $timestamp[0];
		$this->minutes = $timestamp[1];
		$this->seconds = $timestamp[2];

		//Not a notice, do the rest.
		$frags = explode(",", $line);
		//Typecast all of the entries to integers, just in case.
		if(!isset($frags[15])) $frags[10] = trim($frags[10]);

		$this->type_id = $frags[1];

		if(!empty($frags[2])) {
			$tmp = explode("#", $frags[2]);
			$this->origin_type = $tmp[0];
			$this->origin_var1 = $tmp[1];
			$this->origin_id = $tmp[2];
		} else {
			$this->origin_type = false;
			$this->origin_var1 = false;
			$this->origin_id = false;
		}

		if(!empty($frags[3])) {
			$tmp = explode("#", $frags[3]);
			$this->target_type = $tmp[0];
			$this->target_var1 = $tmp[1];
			$this->target_id = $tmp[2];
		} else {
			$this->target_type = false;
			$this->target_var1 = false;
			$this->target_id = false;
		}

		if(!empty($frags[4])) {
			$tmp = explode("#", $frags[4]);
			$this->pet_type = $tmp[0];
			$this->pet_var1 = $tmp[1];
			$this->pet_owner_id = $tmp[2];
		} else {
			$this->pet_type = false;
			$this->pet_var1 = false;
			$this->pet_owner_id = false;
		}
		
		$this->origin_name = !empty($frags[6]) ? trim($this->wordlist[$frags[6]]) : '';
		$this->target_name = !empty($frags[7]) ? trim($this->wordlist[$frags[7]]) : '';
		$this->damage = $frags[8];
		$this->attack_id = $frags[9];
		$this->attack_name = !empty($frags[10]) ? trim($this->wordlist[$frags[10]]) : '';
		
		if(isset($frags[11])) {
			$this->overheal = (int)$frags[11];
			$this->intercepted = (int)$frags[12];
			$this->blocked = (int)$frags[13];
			$this->absorbed = (int)$frags[14];
			$this->overkill = (int)$frags[15];
		} else {
			$this->overheal = 0;
			$this->intercepted = 0;
			$this->blocked = 0;
			$this->absorbed = 0;
			$this->overkill = 0;
		}

		return self::SUCCESS;
	}

	/**
	 * 
	 */
	public function getID( $name )
	{
		$num_bytes = 0;
		while($this->parseLine(true)) {
			$num_bytes += $this->num_bytes;
			if($this->origin_name == $name || $this->target_name == $name) {
				//Rewind and return the value.
				$this->line = $this->lower_limit-1;
				gzseek($this->file_handle, gztell($this->file_handle)-$num_bytes);
				return $this->origin_name == $name ? $this->origin_id : $this->target_id;
			}
		}
		return false;
	}

	public function isPet()
	{
		return $this->origin_type == "N" && $this->pet_type == "P";
	}

	public function getBytes()
	{
		return $this->num_bytes;
	}

	public function isNPC()
	{
		return $this->origin_type == "N" && $this->pet_type != "P";
	}

	public function isPlayer()
	{
		return $this->origin_type == "P";
	}

	public function combatEvent()
	{
		return ($this->origin_type == "P" && $this->target_type == "N") || ($this->origin_type == "N" && $this->target_type == "P");
	}

	public function getSeconds()
	{
		return $this->hours*3600 + $this->minutes*60 + $this->seconds;
	}
}
?>