<h1 class="componentheading">Boss Rankings</h1>

<div class="content-block">
<h2 class="content-heading">Find a Boss</h2>
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
	<a href="<?=base_url()?>rankings/boss/<?=$boss->id?>/" class="raidboss"><?=$boss->boss_name?></a>
<? endforeach; ?>
</div>
<? endforeach; ?>
</div>
<div class="clear"></div>
</div>