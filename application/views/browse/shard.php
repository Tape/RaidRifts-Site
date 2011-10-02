<h1 class="componentheading">Browse Guilds</h1>

<div class="content-block breadcrumbs">
<p>
	<a href="<?=base_url()?>browse/">Browse</a> &raquo;
    <?=$shard->name?>-<?=$shard->region?>
</p>
</div>

<div class="content-block">
<h2 class="content-heading">Guilds in <?=$shard->name?>-<?=$shard->region?></h2>
<? if($shard !== false): ?>
<? foreach($shard->guilds as $guild): ?>
	<a href="<?=base_url()?>browse/guild/<?=$guild->id?>/"><?=htmlentities($guild->name)?></a><br />
<? endforeach; ?>
<? else: ?>
<p>This shard currently does not have any guilds.</p>
<? endif; ?>
</div>