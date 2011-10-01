<h1 class="componentheading">RaidRifts Forum Home</h1>

<? if($boards !== false): ?>
<? foreach($boards as $category_id => &$category): ?>
<div class="content-block">
<h2 class="content-heading chat"><?=$category->name?></h2>
<table class="forum_block boards" cellspacing="0">
<thead>
<tr>
	<th>Description</th>
    <th>Topics</th>
    <th>Posts</th>
    <th>Last Post</th>
</tr>
</thead>
<tbody>
<? foreach($category->boards as &$board): ?>
<tr>
	<td class="left">
		<a href="<?=base_url()?>forums/board/<?=$board->id?>/"><?=$board->name?></a><br />
		<?=$board->description?>
	</td>
    <td><?=$board->count_topics?></td>
    <td><?=$board->count_posts?></td>
    <td>
<? if(!is_null($board->id_lastpost_user)): ?>
		<?=date('n/j/Y h:ia', strtotime($board->date_lastpost))?><br />
		<?=$board->lastpost_username?>
<? else: ?>
		No Posts
<? endif; ?>
    </td>
</tr>
<? endforeach; ?>
</tbody>
</table>
</div>
<? endforeach; ?>
<? else: ?>
<div class="content-block">
<h2 class="content-heading">Whoops!</h2>
<p>It appears there is something wrong with the forums. Please try again later.</p>
</div>
<? endif; ?>