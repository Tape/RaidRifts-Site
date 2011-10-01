<h1 class="componentheading">Browse Guilds</h1>
<div class="content-block">
<h2 class="content-heading">Guild Selection</h2>
<? if($guilds !== false): ?>
<? foreach($guilds as $guild): ?>
	<a href="<?=base_url()?>browse/guild/<?=$guild->id?>/"><?=$guild->name?></a><br />
<? endforeach; ?>
<? else: ?>
<p>This shard currently does not have any guilds.</p>
<? endif; ?>
</div>