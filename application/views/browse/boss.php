<?php
$i = 1 + ($offset - 1) * 100;
?>

<h1 class="componentheading">Boss Rankings</h1>
<div class="content-block">
<h2 class="content-heading">Rankings for <?=$boss_name?></h2>
<? if($encounters !== false && !empty($encounters)): ?>
<table class="data_table ranks" cellspacing="0">
<thead>
<tr>
	<th width="30">Rank</th>
    <th>Guild</th>
    <th>Shard</th>
    <th width="50">Time</th>
</tr>
</thead>
<tbody>
<? foreach($encounters as &$encounter): ?>
<tr class="<?=$i%2 == 0 ? 'even' : 'odd'?>">
	<td align="center"><?=get_rank($i++)?></td>
	<td><a href="<?=base_url()?>report/view/<?=$encounter->hash?>/<?=$encounter->start?>/<?=$encounter->end?>/"><?=$encounter->name?></a></td>
    <td><a href="<?=base_url()?>shard/<?=$encounter->shard_id?>/"><?=$encounter->shard?>-<?=$encounter->region?></a></td>
    <td align="right"><?=seconds_to_minutes($encounter->length)?></td>
</tr>
<? endforeach; ?>
</tbody>
</table>
<? else: ?>
<p>This boss does not have any public attempts logged yet.</p>
<? endif; ?>
</div>