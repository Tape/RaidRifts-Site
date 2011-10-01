<h1 class="componentheading">RaidRifts Forum - <?=$board->name?></h1>

<div class="content-block breadcrumbs">
<p>
	<a href="<?=base_url()?>forums/">Forum Home</a> &raquo;
	<a href="<?=base_url()?>forums/board/<?=$board->id?>/"><?=$board->name?></a> &raquo;
    <?=isset($topic) ? 'New Topic' : 'New Post'?>
</p>
</div>

<? if(isset($error)): ?>
<div class="alert" rel="6"><p><?=$error?></p></div>
<? endif; ?>

<div class="content-block">
<div class="form_wrapper post_form">
<form method="post" action="<?=base_url()?>forums/create/<?=$board->id?>/" class="post_form">
<h3>Create New Topic</h3>
<p>
	Title:<br />
	<input type="text" name="title" value="<?=isset($error) ? $post['title'] : ''?>" />
</p>
<p>
	Message Body:<br />
	<textarea name="body"><?=isset($error) ? $post['body'] : ''?></textarea>
</p>
<p>
	<a class="btn submit" style="margin-right:5px"><span>Create Topic</span></a>
	<a class="btn"><span>Preview Post</span></a>
</p>
</form>
</div>
</div>