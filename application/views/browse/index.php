<h1 class="componentheading">Browse RaidRifts</h1>

<div class="content-block">
<h2 class="content-heading">Browse for Guilds</h2>
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