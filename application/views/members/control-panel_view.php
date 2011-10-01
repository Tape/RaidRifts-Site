<?php
//Grab the user currently logged in.
$user =& $this->user;

$in_guild = $guild_info !== false;
$can_edit = $user->has_permission(ACCESS_LOG_EDIT);
$can_delete = $user->has_permission(ACCESS_LOG_REMOVE);
$can_invite = $user->has_permission(ACCESS_GUILD_ADD);
$can_kick = $user->has_permission(ACCESS_GUILD_REMOVE);
$can_promote = $user->has_permission(ACCESS_GUILD_PROMOTE);
$is_guild_leader = $user->has_permission(ACCESS_GUILD_LEADER);
?>
<? if($user->id !== false): ?>
<h1 class="componentheading">Control Panel</h1>
<? if(isset($error)): ?>
<div class="alert">
	<p><?=$error?></p>
</div>
<? endif;if(isset($notification)): ?>
<div class="notification">
	<p><?=$notification?></p>
</div>
<? endif; ?>
<div class="content-block">
<h2 class="content-heading note"><span class="icon-large expand float-right shrinkable"></span>Guild Settings<?=$in_guild ? ' - '.$guild_info->name : ''?></h2>
<div class="inner hide guild_settings">
<? if($in_guild): ?>
<? if($is_guild_leader): ?>
<div class="col padded">
<div class="form_wrapper">
<form method="post" action="<?=base_url()?>members/controlpanel/">
<h3>Administration</h3>
<p>
	Guild Name:<br />
    <input type="text" name="name" value="<?=$guild_info->name?>" />
</p>
<p>
	Shard:<br />
    <?=form_dropdown('shard', $shards, $guild_info->shard)?>
</p>
<p>
	Faction:<br />
    <?=form_dropdown('faction', array('Defiant' => 'Defiant', 'Guardian' => 'Guardian'), $guild_info->faction)?>
</p>
<p>
	Website:<br />
    <input type="text" name="website" value="<?=$guild_info->website?>" />
</p>
<p>
	Default Log Privacy:<br />
    <?=form_dropdown('default_privacy', array('Public', 'Private'), $guild_info->default_privacy)?>
</p>
<p>
	Pass Leadership:<br />
    <?=form_dropdown('leader', $leader_change_list)?>
</p>
<p><a class="btn submit" href="#"><span><img src="<?=base_url()?>images/icons/save.gif">Save Changes</span></a></p>
<input type="hidden" name="type" value="guild-settings">
</form>
</div>
</div>
<? endif; ?>
<? if($can_invite): ?>
<div class="col padded">
<div class="form_wrapper">
<h3>Applications</h3>
<? if($applications): ?>
<p class="center">Mouse over an application to view the message.</p>
<table class="data_table applications" cellspacing="0">
<thead>
<tr>
	<th>Name</th>
	<th>Date</th>
	<th width="15">&nbsp;</th>
	<th width="15">&nbsp;</th>
</tr>
</thead>
<tbody>
<? foreach($applications as $application): ?>
<tr rel="<?=nl2br(htmlspecialchars($application->message))?>" ajax="<?=$application->id?>">
	<td><?=$application->username?></td>
	<td><?=date("m/j/Y", strtotime($application->date))?></td>
	<td align="center"><a class="icon check" href="#" rel="accept"></a></td>
	<td align="center"><a class="icon cross" href="#" rel="delete"></a></td>
</tr>
<? endforeach; ?>
</tbody>
</table>
<? else: ?>
<p>There are currently no guild applications.</p>
<? endif; ?>
</div>
</div>
<? endif; ?>
<div class="col padded member_list">
<div class="form_wrapper">
<h3>Members</h3>
<table class="data_table" cellspacing="0">
<thead>
<tr>
	<th>Rank</th>
	<th>Name</th>
	<th>Joined</th>
<? if($can_kick): ?>
	<th width="15">&nbsp;</th>
<? endif; ?>
</tr>
</thead>
<tbody>
<? foreach($members as $member): ?>
<tr rel="<?=$member->id?>">
	<td>
	<? if($can_promote && $member->rank != 'Leader'): ?>
		<a href="#" class="icon ilb plus"<?=$member->rank == 'Officer' ? ' style="display:none"' : ''?>></a>
		<a href="#" class="icon ilb minus"<?=$member->rank == 'Member' ? ' style="display:none"' : ''?>></a>
	<? endif; ?>
		<span><?=$member->rank?></span>
	</td>
	<td><?=$member->username?></td>
	<td><?=date("m/j/Y", strtotime($member->joined))?></td>
<? if($can_kick): ?>
	<td align="center"><? if($member->rank != 'Leader'): ?><a href="#" class="icon cross"></a><? endif; ?></td>
<? endif; ?>
</tr>
<? endforeach; ?>
</tbody>
</table>
<form method="post" action="<?=base_url()?>members/controlpanel/">
<? if($can_promote && count($members) > 1): ?>
<p class="right"><a class="btn submit" style="display:none" href="#" id="member_changes"><span><img src="<?=base_url()?>images/icons/save.gif">Save Changes</span></a></p>
<input type="hidden" name="type" value="manage-members">
<? else: ?>
<p class="right">
	<a class="btn submit" rel="Are you sure you want to leave this guild?" href="#"><span>Leave Guild</span></a>
</p>
<input type="hidden" name="type" value="leave-guild">
<? endif; ?>
</form>
</div>
</div>
<? else: ?>
<div class="col padded">
<div class="form_wrapper">
<h3>Create Guild</h3>
<form method="post" action="<?=base_url()?>members/controlpanel/">
<p>
	Shard:<br />
	<?=form_dropdown('shard', $shards)?>
</p>
<p>
	Guild Name:<br />
	<input type="text" name="guildname" />
</p>
<p>
	<a class="btn submit" href="#"><span>Submit</span></a>
</p>
<input type="hidden" name="type" value="add-guild">
</form>
</div>
</div>
<div class="col padded">
<div class="form_wrapper">
<h3>Apply to Guild</h3>
<form method="post" action="<?=base_url()?>members/controlpanel/">
<p>
	Shard:<br />
	<?=form_dropdown('shard', array('--') + $shards, null, ' onchange="load_guilds(this.value)"')?>
</p>
<div id="apply_wrapper">
<p><span></span></p>
<div></div>
</div>
<div id="apply_controls" style="display:none">
<p>
	Message:<br />
	<textarea rows="6" name="message"></textarea>
</p>
<p>
	<a class="btn submit" href="#"><span>Submit</span></a>
</p>
</div>
<input type="hidden" name="type" value="apply">
</form>
</div>
</div>
<? endif; ?>
<div class="clear"></div>
</div>
</div>

<div class="content-block">
<h2 class="content-heading"><span class="icon-large expand float-right shrinkable"></span>Logs</h2>
<div class="inner hide logs">
<? if(isset($body_vars) && $body_vars !== false): ?>
<form method="post" action="<?=base_url()?>members/controlpanel/">
<? if($can_edit): ?>
<p class="right"><a class="btn submit" style="display:none" href="#" id="save_changes"><span><img src="<?=base_url()?>images/icons/save.gif">Save Changes</span></a></p>
<? endif; ?>
<table class="data_table sorter" cellspacing="0">
<thead>
<tr>
	<th>Status</th>
	<th width="66">Log ID</th>
	<th width="35" class="disabled">Public</th>
	<th width="293" class="disabled">Log Notes</th>
	<th>Raid Date</th>
<? if($can_edit): ?>
	<th class="disabled">&nbsp;</th>
<? endif; ?>
<? if($can_delete): ?>
	<th class="disabled">&nbsp;</th>
<? endif; ?>
</tr>
</thead>
<tbody>
<?php foreach($body_vars as &$row): ?>
<tr>
	<td><?=$row->processed == 1 ? 'Live' : ($row->processed == 0 ? 'Processing' : ($row->processed == 2 ? 'Corrupted' : 'Empty'))?></td>
	<td><?=$row->processed == 1 ? '<a href="'.base_url().'report/view/'.$row->hash.'" class="glow">'.$row->hash.'</a>' : $row->hash?></td>
	<td align="center"><span class="icon<?=$row->private == 0 ? ' check' : ''?>"></span></td>
	<td><?=$row->notes?></td>
	<td align="right"><?=date("m/j/Y", strtotime($row->raid_date))?></td>
<? if($can_edit): ?>
	<td align="center"><span rel="<?=$row->id?>" class="icon edit"></span></td>
<? endif; ?>
<? if($can_delete): ?>
	<td align="center"><span rel="<?=$row->id?>" class="icon delete"></span></td>
<? endif; ?>
</tr>
<? endforeach; ?>
</tbody>
</table>
<input type="hidden" name="type" value="edit-logs">
</form>
<? else: ?>
<p>Your guild currently does not have any logs uploaded.</p>
<? endif; ?>
</div>
</div>

<div class="content-block">
<h2 class="content-heading note"><span class="icon-large expand float-right shrinkable"></span>Account Settings</h2>
<div class="inner hide">
<div class="col padded">
<div class="form_wrapper">
<form method="post" action="<?=base_url()?>members/controlpanel/">
<h3>Password Change</h3>
<p>
	Old password:<br />
	<input type="password" name="old_password">
</p>
<p>
	New password:<br />
	<input type="password" name="new_password1">
</p>
<p>
	New password (confirm):<br />
	<input type="password" name="new_password2">
</p>
<p class="right">
	<a class="btn submit" href="#"><span><img src="<?=base_url()?>images/icons/save.gif">Save Changes</span></a>
</p>
<input type="hidden" name="type" value="account-settings">
</form>
</div>
</div>
<div class="clear"></div>
</div>
</div>
<?
else:
	$this->load->view('restricted');
endif;
?>