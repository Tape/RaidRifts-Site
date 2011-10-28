<?php
include('config.php');
$filepath = $include_path.'combat_logs'.DS.$argv[1].".gz";

//Connect to mysql.
mysql_connect($mysql_host, $mysql_user, $mysql_pass);
mysql_select_db($mysql_db);

//Create the parser.
$fh = gzopen($filepath, 'r');

//Log and line stuff.
$v = new stdClass;
$v->line = 0;
$v->byte_count = 0;
$params = array();

//Discard the first line.
gzgets($fh);

//Read each line and record milestones.
while($str = gzgets($fh)) {
	$v->line++;
	$v->byte_count += strlen($str);
	if($v->line%100000 == 0) $params[] = $v->byte_count;
}

//Close the file handler.
gzclose($fh);

//Convert to json and update the database.
$json = mysql_real_escape_string(json_encode($params));
mysql_query("UPDATE `logs` SET `processed` = 1, `vars` = '".$json."' WHERE `hash` = '".$argv[1]."' LIMIT 1");
mysql_close();
?>
