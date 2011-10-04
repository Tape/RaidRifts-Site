<h1 class="componentheading">Edit Page</h1>

<? if(isset($notification)): ?>
<div class="notification"><p><?=$notification?></p></div>
<? elseif(isset($error)): ?>
<div class="alert"><p><?=$error?></p></div>
<? endif; ?>

<div class="content-block">
<h2 class="content-heading edit">Editing "<?=$page->name?>"</h2>
<form method="post" action="<?base_url()?>page/edit/<?=$page->id?>/">
<textarea style="width:600px;height:500px;margin:auto" name="content"><?=$page->content?></textarea>
<p class="right">
	<a class="btn submit"><span>Save Changes</span></a>
</p>
</form>
</div>