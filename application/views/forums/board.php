<h1 class="componentheading">RaidRifts Forum - <?=$board->name?></h1>

<div class="content-block breadcrumbs">
<p><a href="<?=base_url()?>forums/">Forum Home</a> &raquo; <?=$board->name?></p>
</div>

<div class="content-block">
<h2 class="content-heading">
<? if($this->user->id !== false): ?>
	<a class="btn float-right" href="<?=base_url()?>forums/create/<?=$board->id?>/"><span><img src="<?=base_url()?>images/icons/create.gif" />Start Topic</span></a>
<? endif; ?>
	Topics in <?=$board->name?>
</h2>
<? if($topics !== false): ?>
<table class="forum_block" cellspacing="0">
<thead>
<tr>
	<th>Title</th>
    <th>Posts</th>
    <th>Views</th>
    <th>Last Post</th>
</tr>
</thead>
<tbody>
<? foreach($topics as &$topic): ?>
<tr>
	<td class="left">
		<a href="<?=base_url()?>forums/topic/<?=$topic->id?>/"><?=$topic->title?></a><br />
		Started by <?=$topic->admin ? '<span class="legendary">' : '<span class="uncommon">'?><?=$topic->username?></span>, <?=date('n/j/Y h:ia', strtotime($topic->date_inserted))?>
	</td>
	<td><?=$topic->post_count?></td>
	<td><?=$topic->views?></td>
	<td><?=is_null($topic->id_lastpost) ? 'No replies' : ''?></td>
</tr>
<? endforeach; ?>
</tbody>
</table>
<? else: ?>
<p>Whoops! Looks like there's no threads here. :(</p>
<? endif; ?>
</div>