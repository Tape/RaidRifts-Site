<h1 class="componentheading">Browse RaidRifts</h1>

<div class="content-block">
<h2 class="content-heading"><span class="icon-large expand float-right shrinkable"></span>Guilds</h2>
<div class="inner hide">
<p>Pick a shard from the below list to view guilds in it.</p>
<div class="col padded">
<div class="form_wrapper">
<h3>North America</h3>
<p>
<? foreach($na_servers as $server): ?>
	<a href="<?=base_url()?>browse/shard/<?=$server->id?>/"><?=$server->name?>-<?=$server->region?></a><br />
<? endforeach; ?>
</p>
</div>
</div>
<div class="col padded">
<div class="form_wrapper">
<h3>Europe</h3>
<p>
<? foreach($eu_servers as $server): ?>
	<a href="<?=base_url()?>browse/shard/<?=$server->id?>/"><?=$server->name?>-<?=$server->region?></a><br />
<? endforeach; ?>
</p>
</div>
</div>
<div class="clear"></div>
</div>
</div>

<div class="content-block">
<h2 class="content-heading"><span class="icon-large expand float-right shrinkable"></span>Raids</h2>
<div class="inner hide">
<div class="col padded">
<?php
$count = count($raids);
$num = ceil($count/2);
$i = 0;

foreach($raids as $raid_name => &$raid):
	if($i++ == $num): ?>
</div>
<div class="col padded">
<? endif; ?>
<div class="form_wrapper<?=($i > $num && $i != $count) || ($i < $num) ? ' spaced' : ''?>">
	<h3><?=$raid_name?></h3>
<? foreach($raid as &$boss): ?>
	<a href="<?=base_url()?>browse/boss/<?=$boss->id?>/" class="raidboss"><?=$boss->boss_name?></a>
<? endforeach; ?>
</div>
<? endforeach; ?>
</div>
<div class="clear"></div>
</div>
</div>