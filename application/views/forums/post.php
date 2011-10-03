<h1 class="componentheading">RaidRifts Forum - <?=$is_topic ? $board->name : 'RE: '.$topic->title?></h1>

<div class="content-block breadcrumbs">
<p>
	<a href="<?=base_url()?>forums/">Forum Home</a> &raquo;
<? if($is_topic): ?>
	<a href="<?=base_url()?>forums/board/<?=$board->id?>/"><?=$board->name?></a> &raquo;
    New Topic
<? else: ?>
	<a href="<?=base_url()?>forums/board/<?=$topic->board_id?>/"><?=$topic->board_name?></a> &raquo;
	<a href="<?=base_url()?>forums/topic/<?=$topic->id?>/"><?=$topic->title?></a> &raquo;
    New Post
<? endif; ?>
</p>
</div>

<? if(isset($error)): ?>
<div class="alert" rel="6"><p><?=$error?></p></div>
<? endif; ?>

<div class="content-block">
<div class="form_wrapper post_form">
<? $action = $is_topic ? base_url().'forums/create/'.$board->id : base_url().'forums/post/'.$topic->id; ?>
<form method="post" action="<?=$action?>/" class="post_form">
<h3>Create New <?=$is_topic ? 'Topic' : 'Post'?></h3>
<p>
	Title:<br />
	<input type="text" name="title" value="<?=isset($error) ? $post['title'] : ''?>" />
</p>
<p>
	Message Body:<br />
	<textarea name="body"><?=isset($error) ? $post['body'] : ''?></textarea>
</p>
<p>
	<a class="btn submit" style="margin-right:5px"><span>Create <?=$is_topic ? 'Topic' : 'Post'?></span></a>
	<a class="btn"><span>Preview Post</span></a>
</p>
</form>
</div>
</div>