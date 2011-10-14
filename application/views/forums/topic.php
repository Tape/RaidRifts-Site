<h1 class="componentheading">RaidRifts Forum - Viewing Topic</h1>

<div class="content-block breadcrumbs">
<p><a href="<?=base_url()?>forums/">Forum Home</a> &raquo; <a href="<?=base_url()?>forums/board/<?=$topic->id_board?>/"><?=$topic->name?></a> &raquo; <?=$topic->title?></p>
</div>

<div class="content-block">
<h2 class="content-heading">
	<?=$topic->title?>
</h2>
<? if($posts !== false): ?>
<? foreach($posts as $i => &$post): ?>
<table class="forum_block post" cellspacing="0">
<thead>
<tr>
	<th colspan="2"><?=date("n/j/Y h:ia", strtotime($post->date_inserted))?></th>
</tr>
</thead>
<tbody>
<tr>
<td class="user_info">
<? if($post->admin): ?>
	<span class="legendary"><?=htmlentities($post->username)?></span><br />
<? else: ?>
	<span class="uncommon"><?=htmlentities($post->username)?></span><br />
<? endif; ?>
	<?=$post->admin ? 'Site Administrator' : 'Member'?><br />
	Joined: <?=date("M j, Y", strtotime($post->joined))?><br />
	Posts: <?=$post->posts?>
</td>
<td>
<? if(false)://if($post->id_user == $this->user->id): ?>
	<a href="<?=base_url()?>forums/<?=$i == 0 ? 'topic' : 'post'?>/<?=$post->id?>/edit/"><img src="<?=base_url()?>images/icons/create.gif" class="float-right" /></a>
<? endif; ?>
	<p class="board_title"><b class="glow">Title:</b> <?=empty($post->title) ? 're: '.$topic->title : $post->title?></p>
	<?=nl2br($post->body)?>
</td>
</tr>
</tbody>
</table>
<? endforeach; ?>
<? else: ?>
<p>Whoops! Looks like there's no topic here. :(</p>
<? endif; ?>
<div class="clear"></div>
</div>

<? if($this->user->id !== false): ?>
	<a class="btn float-right" href="<?=base_url()?>forums/post/<?=$topic->id?>/"><span><img src="<?=base_url()?>images/icons/create.gif" />Post Reply</span></a>
<? endif; ?>