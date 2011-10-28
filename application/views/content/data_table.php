<?php
//Comparison function for tooltip stuff.
function tooltip_cmp($a, $b)
{
	if($a == $b) return 0;
	return $a > $b ? -1 : 1;
}
?>
<script type="text/javascript">
var v={l:'<?=$log_id?>',s:<?=$log_start?>,e:<?=$log_end?>},m=[<?php
//Generate the markings.
$tmp = array();
foreach($body_vars->markings as $i => $m) {
	$tmp[] = sprintf('{i:%d,t:%d,l:%d,n:"%s",o:"%s",c:"%s"}', $m['type'], $m['time'], $m['length'], $m['actor'], $m['origin'], $m['color']);
}
echo implode(',', $tmp);
unset($tmp);

//Generate tooltips.
?>],t={<?php
foreach(array('dmg' => false, 'heal' => false, 'taken' => false) as $type => $comma) {
	switch($type) {
		case 'dmg': echo 'damage:[';break;
		case 'heal': echo ',healing:[';break;
		case 'taken': echo ',taken:[';break;
	}
	foreach($body_vars->breakdown as $actor_id => &$types) {
		if($types['totals'][$type] == 0) continue;
		if($comma) echo ",";
		else $comma = true;
		//Have a pet?
		$has_pet = isset($types['pet'][$type]) && $type != 'taken';
		//Sort the array.
		uasort($types[$type], 'tooltip_cmp');
		//Sort for the pet too if necessary.
		if($has_pet) uasort($types['pet'][$type], 'tooltip_cmp');

		printf('{name:"%s",total:%d,values:[', $types['name'], $types['totals'][$type] - ($has_pet ? $types['pet']['totals'][$type] : 0));
		$tmp = array();
		foreach($types[$type] as $ability_name => &$value) $tmp[] = sprintf('["%s",%d]', $ability_name, $value);
		echo implode(",", $tmp)."]";
		unset($tmp);
		
		//Add the pet.
		if($has_pet) {
			echo ',pet:{name:"'.$types['pet']['name'].'",total:'.$types['pet']['totals'][$type].',values:[';
			$tmp = array();
			foreach($types['pet'][$type] as $pet_attack => &$value) $tmp[] = sprintf('["%s",%d]', $pet_attack, $value);
			echo implode(",", $tmp)."]}";
			unset($tmp);
		}
		echo "}";
	}
	echo "]";
}
?>},g={<?php
foreach(array('dmg', 'heal', 'taken') as $type) {
	//Output which type of thing we are doing...
	switch($type) {
	case 'dmg':echo 'damage:{';break;
	case 'heal':echo ',healing:{';break;
	case 'taken':echo ',taken:{';break;
	}
	
	//Output the total information.
	echo '"total":{label:"Total",data:[';
	if(count($body_vars->graph[$type]) > 0) {
		$tmp = array();
		foreach($body_vars->graph[$type] as $i => &$point) $tmp[] = sprintf("[%d,%d]", $i, $point);
		echo implode(",", $tmp);
		unset($tmp);
	}
	echo '],yaxis:2}';
	
	//Now we move onto the individual player stuff.
	foreach($body_vars->breakdown as $actor_id => &$actor) {
		//Skip empty sets.
		if(!isset($actor['graph'][$type])) continue;

		//Random data..
		$tmp = array();
		printf(',"%s":{label:"%s",data:[', strtolower($actor['name']), $actor['name']);
		foreach($actor['graph'][$type] as $i => &$point) $tmp[] = sprintf("[%d,%d]", $i, $point);
		echo implode(",", $tmp);
		unset($tmp);
		echo ']}';
	}
	echo "}";
}
?>};
</script>

<h1 class="componentheading">Parse by <?=htmlentities($body_vars->guild_name)?> on <?=date('m/j/Y', strtotime($body_vars->date))?></h1>

<div class="content-block">
<h2 class="content-heading chat">(<?=seconds_to_minutes($body_vars->l)?>) <?=$body_vars->encounter_boss?> - <?=$body_vars->l?>s</h2>

<div id="tabs">
<ul class="ui-helper-clearfix">
	<li><a href="#tabs-1">Damage</a></li>
	<li><a href="#tabs-2">Healing</a></li>
	<li><a href="#tabs-3">Damage Taken</a></li>
</ul>
<?php
$i = 1;
foreach(array('dmg', 'heal', 'taken') as $type):
	$out = null;
	switch($type) {
	case 'dmg':$out = array('damage', 'Damage', 'DPS');break;
	case 'heal':$out = array('healing', 'Healing', 'HPS');break;
	case 'taken':$out = array($type, 'Damage Taken', 'DPS');break;
	}
?>
<div id="tabs-<?=$i++?>">
<div class="graph_canvas" rel="<?=$out[0]?>"></div>
<div class="graph_icons">
	<div class="graph_icon" onclick="toggle_markings(this, '<?=$out[0]?>', [0, 1])" title="Player Deaths"></div>
	<div class="graph_icon event" onclick="toggle_markings(this, '<?=$out[0]?>', 2)" title="Raid Events"></div>
    <div class="clear"></div>
</div>
<div class="graph_legend" rel="<?=$out[0]?>"></div>
<table cellspacing="0" class="sortable data_table" rel="<?=$out[0]?>">
<thead>
<tr>
	<th><input type="checkbox" onclick="checkAll('<?=$out[0]?>', this)" /></th>
	<th width="125">Name</th>
	<th colspan="3"><?=$out[1]?></th>
	<th width="40"><?=$out[2]?></th>
	<th width="40"><?=$out[2]?>(e)</th>
	<th colspan="2" width="75">Time</th>
</tr>
</thead>
<tbody>
<?php
$dps_total = 0;
foreach($body_vars->breakdown as $actor_id => &$types):
	if($types['totals'][$type] == 0) continue;
?>
<tr>
	<td align="center"><input type="checkbox" rel="<?=$out[0]?>" name="<?=strtolower($types['name'])?>" /></td>
	<td><a href="#"><?=$types['name']?></a></td>
	<td align="right"><?=$types['totals'][$type]?></td>
	<td align="right"><?=format_number($types['totals'][$type]/$body_vars->total[$type]*100, 1, ".", '').'%'?></td>
	<td width="180"><div class="percent_visual" rel="<?=round($types['totals'][$type]/$body_vars->maximum[$type]*100)?>"></div></td>
	<td align="right"><?=$val = $types['activity'][$type]['total'] == 0 ? 0 : format_number($types['totals'][$type]/$types['activity'][$type]['total'])?></td>
	<td align="right"><?=format_number($types['totals'][$type]/$body_vars->l)?></td>
	<td align="right"><?=$types['activity'][$type]['total']?></td>
	<td align="right"><?=format_number($types['activity'][$type]['total']/$body_vars->l*100)?>%</td>
</tr>
<?php
	$dps_total += $val;
endforeach;
?>
</tbody>
<tfoot>
<tr>
	<th align="center"><input type="checkbox" rel="<?=$out[0]?>" name="total" checked="checked" disabled="disabled" /></th>
	<th align="left">Totals</th>
	<th align="right"><?=$body_vars->total[$type]?></th>
	<th align="right">100%</th>
	<th>&nbsp;</th>
	<th><?=$dps_total?></th>
	<th align="right"><?=format_number($body_vars->total[$type]/$body_vars->l)?></th>
	<th align="right"><?=$body_vars->l?>s</th>
	<th align="right">100%</th>
</tr>
</tfoot>
</table>
</div>
<?php endforeach; ?>
</div>
</div>