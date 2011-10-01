<?php
function format_number( $val )
{
	return number_format($val, 1, '.', '');
}

function seconds_to_minutes( $seconds )
{
	$sec = $seconds % 60;
	return floor($seconds / 60).":".($sec < 10 ? "0" : '').$sec;
}

function time_ago($tm, $rcs = 0)
{
	$cur_tm = time(); $dif = $cur_tm-$tm;
	$pds = array('s','m','h','d','w');
	$lngh = array(1,60,3600,86400,604800);
	for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

	$x=sprintf("%d%s ",$no,$pds[$v]);
	if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
	return $x;
}

function str_rand( $length = 10 )
{
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
	$size = strlen($chars);
	$str = '';
	for($i = 0; $i < $length; $i++) {
		$str .= $chars[rand(0, $size-1)];
	}

	return $str;
}

function get_rank( $num )
{
	switch(true) {
	case $num == 1:
		return '<span class="legendary bold">'.$num.'</span>';
		break;
	case $num <= 5:
		return '<span class="epic bold">'.$num.'</span>';
		break;
	case $num <= 20:
		return '<span class="rare bold">'.$num.'</span>';
		break;
	default:
		return '<span class="uncommon bold">'.$num.'</span>';
		break;
	}
}
?>