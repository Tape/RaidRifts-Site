<?php
$can_apply = $this->user->id !== false && $this->user->guild == 0;
?>
<h1 class="componentheading">Browse Guilds</h1>
<div class="content-block">
<h2 class="content-heading"><?=$body_vars->name?> - <?=$body_vars->faction?></h2>
<? if($can_apply): ?>
<p class="right"><a id="apply_link" class="btn" href="#"><span>Submit Application</span></a></p>
<? endif; ?>
<?=$calendar?>
</div>
<? if($can_apply): ?>
<div id="apply_box">
<form method="post" action="<?=base_url()?>members/ajax/apply/">
<p>
	Message:<br />
    <textarea name="message" rows="10"></textarea>
</p>
<p><a class="btn submit" href="#"><span>Submit</span></a></p>
<input type="hidden" name="gid" value="<?=$guild_id?>" />
</form>
</div>
<? endif; ?>