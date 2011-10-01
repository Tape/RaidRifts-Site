<? if(sizeof($data->abilities['dmg']) > 0): ?>
<h3>Damage Done</h3>
<table class="data_table" cellspacing="0">
<thead>
<tr>
	<th>Spell</th>
	<th colspan="2">Damage</th>
	<th>DPS(e)</th>
	<th colspan="2">Hits</th>
	<th colspan="2">Crits</th>
	<th>Max</th>
	<th>Avg</th>
	<th>Min</th>
	<th colspan="2">Misses</th>
</tr>
</thead>
<tbody>
<?
$k = 0;
foreach($data->abilities['dmg'] as $name => &$vars):
	$k = 1 - $k;
?>
<tr class="<?=$k == 0 ? "even" : "odd"?>">
	<td><a href="http://rift.zam.com/en/ability/<?=$vars['id']?>/" target="_blank"><?=$name?></a></td>
	<td align="right"><?=$vars['total']?></td>
	<td align="right"><?=format_number($vars['total']/$data->totals['dmg']*100)?>%</td>
	<td align="right"><?=format_number($vars['total']/$data->l)?></td>
	<td align="right"><?=$vars['cast']-$vars['misses']?></td>
	<td align="right"><?=format_number(($vars['cast']-$vars['misses'])/$vars['cast']*100)?>%</td>
	<td align="right"><?=$vars['crits']?></td>
	<td align="right"><?=format_number($vars['crits']/$vars['cast']*100)?>%</td>
	<td align="right"><?=$vars['max']?></td>
	<td align="right"><?=format_number($vars['cast'] == $vars['misses'] ? 0 : $vars['total']/($vars['cast']-$vars['misses']))?></td>
	<td align="right"><?=$vars['min']?></td>
	<td align="right"><?=$vars['misses']?></td>
	<td align="right"><?=$vars['misses'] == 0 ? '0.0' : format_number($vars['misses']/$vars['cast']*100)?>%</td>
</tr>
<? endforeach; ?>
</tbody>
<tfoot>
<tr>
	<th align="right">Total</th>
    <th align="right"><?=$data->totals['dmg']?></th>
    <th align="right">100.0%</th>
    <th align="right"><?=format_number($data->totals['dmg']/$data->l)?></th>
    <th colspan="9">&nbsp;</th>
</tr>
</tfoot>
</table>
<? endif; ?>

<? if(sizeof($data->abilities['heal']) > 0): ?>
<h3>Healing Done</h3>
<table class="data_table" cellspacing="0">
<thead>
<tr>
	<th>Spell</th>
	<th colspan="2">Effective Healing</th>
	<th>HPS(e)</th>
	<th colspan="2">Overheal</th>
    <th>Cast</th>
	<th colspan="2">Crits</th>
	<th>Max</th>
	<th>Avg</th>
	<th>Min</th>
</tr>
</thead>
<tbody>
<?
$k = 0;
foreach($data->abilities['heal'] as $name => &$vars):
	$k = 1 - $k;
?>
<tr class="<?=$k == 0 ? "even" : "odd"?>">
	<td><a href="http://rift.zam.com/en/ability/<?=$vars['id']?>/" target="_blank"><?=$name?></a></td>
	<td align="right"><?=$vars['total']?></td>
	<td align="right"><?=$data->totals['heal'] > 0 ? format_number($vars['total']/$data->totals['heal']*100) : '0.0'?>%</td>
    <td align="right"><?=format_number($vars['total']/$data->l)?></td>
	<td align="right"><?=$vars['overheal']?></td>
	<td align="right"><?=format_number($vars['overheal']/$vars['total_modified']*100)?>%</td>
    <td align="right"><?=$vars['cast']?></td>
	<td align="right"><?=$vars['crits']?></td>
	<td align="right"><?=format_number($vars['crits']/$vars['cast']*100)?>%</td>
	<td align="right"><?=$vars['max']?></td>
	<td align="right"><?=format_number($vars['total']/$vars['cast'])?></td>
	<td align="right"><?=$vars['min']?></td>
</tr>
<? endforeach; ?>
</tbody>
<tfoot>
<tr>
	<th align="right">Total</th>
	<th align="right"><?=$data->totals['heal']?></th>
	<th align="right">100%</th>
	<th align="right"><?=format_number($data->totals['heal']/$data->l)?></th>
    <th colspan="8">&nbsp;</th>
</tr>
</tfoot>
</table>
<? endif; ?>

<? if(sizeof($data->abilities['taken']) > 0): ?>
<h3>Damage Taken</h3>
<table class="data_table damage_taken" cellspacing="0">
<thead>
<tr>
	<th rowspan="2">Spell</th>
	<th colspan="2">Damage Taken</th>
	<th rowspan="2">DPS(e)</th>
    <th colspan="2">Hits</th>
    <th colspan="3">Blocks</th>
    <th rowspan="2">Max</th>
    <th rowspan="2">Avg</th>
    <th rowspan="2">Min</th>
</tr>
<tr>
	<th>Total</th>
    <th>%</th>
	<th>#</th>
    <th>%</th>
    <th>#</th>
	<th>Avg</th>
    <th>Total</th>
</tr>
</thead>
<tbody>
<?
$k = 0;
$tip_data = array();
foreach($data->abilities['taken'] as $name => &$vars):
	$k = 1 - $k;
	$row_rel = implode(';', array(
		'Hit:'.format_number(($vars['cast'] - $vars['misses'])/$vars['cast']*100),
		'Miss:'.format_number(($vars['misses'] - $vars['dodge'] - $vars['parry'])/$vars['cast']*100),
		'Dodge:'.format_number($vars['dodge']/$vars['cast']*100),
		'Parry:'.format_number($vars['parry']/$vars['cast']*100)
	));
?>
<tr class="<?=$k == 0 ? "even" : "odd"?>" rel="<?=$row_rel?>">
	<td><a href="http://rift.zam.com/en/ability/<?=$vars['id']?>/" target="_blank"><?=$name?></a></td>
	<td align="right"><?=$vars['total']?></td>
	<td align="right"><?=$data->totals['heal'] > 0 ? format_number($vars['total']/$data->totals['taken']*100) : '0.0'?>%</td>
    <td align="right"><?=format_number($vars['total']/$data->l)?></td>
	<td align="right"><?=$vars['cast']-$vars['misses']?></td>
	<td align="right"><?=format_number(($vars['cast']-$vars['misses'])/$vars['cast']*100)?>%</td>
    <td align="right"><?=$vars['block_count']?></td>
    <td align="right"><?=$vars['block_count'] > 0 ? format_number($vars['block_total']/$vars['block_count']) : 0?></td>
    <td align="right"><?=$vars['block_total']?></td>
	<td align="right"><?=$vars['max']?></td>
	<td align="right"><?=format_number($vars['cast'] == $vars['misses'] ? 0 : $vars['total']/($vars['cast']-$vars['misses']))?></td>
	<td align="right"><?=$vars['min']?></td>
</tr>
<? endforeach; ?>
</tbody>
<tfoot>
<tr>
	<th align="right">Total</th>
	<th align="right"><?=$data->totals['taken']?></th>
	<th align="right">100%</th>
	<th align="right"><?=format_number($data->totals['taken']/$data->l)?></th>
    <th colspan="8">&nbsp;</th>
</tr>
</tfoot>
</table>
<? endif; ?>

<? if(sizeof($data->deaths) > 0): ?>
<h3>Deaths</h3>
<? foreach($data->deaths as $i => &$lines): ?>
<h4 style="padding-top:10px">Death <?=$i+1?></h4>
<table cellspacing="0" class="data_table">
<thead>
	<tr><th>Offset</th><th>Health</th><th>Action</th></tr>
</thead>
<tbody>
<? foreach($lines as &$line): ?>
<tr>
	<td align="right"><b><?=$line[0]?>s</b></td>
	<td align="right"><?=$line[1]?></td>
	<td><?=$line[2]?></td>
</tr><?php endforeach; ?>
</tbody>
</table>
<? endforeach; ?>
<? endif; ?>
<script type="text/javascript" src="<?=base_url()?>js/player<?=IS_LOCAL_TEST ? '.min' : '.min'?>.js"></script>
<script type="text/javascript" src="http://common.zam.com/shared/zampower.js"></script>