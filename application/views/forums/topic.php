<h1 class="componentheading">RaidRifts Forum - Viewing Topic</h1>

<div class="content-block breadcrumbs">
<p><a href="<?=base_url()?>forums/">Forum Home</a> &raquo; <a href="<?=base_url()?>forums/board/<?=$topic->id_board?>/"><?=$topic->name?></a> &raquo; <?=$topic->title?></p>
</div>

<div class="content-block">
<h2 class="content-heading">
<? if($this->user->id !== false): ?>
	<a class="btn float-right" href="<?=base_url()?>forums/post/<?=$topic->id?>/"><span><img src="<?=base_url()?>images/icons/create.gif" />Post Reply</span></a>
<? endif; ?>
	<?=$topic->title?>
</h2>
<? if($posts !== false): ?>
<? foreach($posts as &$post): ?>
<div><?=$post->title?></div>
<div><?=$post->body?></div>
<? endforeach; ?>
<? else: ?>
<p>Whoops! Looks like there's no threads here. :(</p>
<? endif; ?>
<a class="btn float-right" href="<?=base_url()?>forums/post/<?=$topic->id?>/"><span><img src="<?=base_url()?>images/icons/create.gif" />Post Reply</span></a>
<div class="clear"></div>
</div>