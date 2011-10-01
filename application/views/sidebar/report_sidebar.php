<h2 class="sidebarheading">Report Features</h2>
<div class="content-block">
<h2>Boss List</h2>
<?php if(!empty($sidebar_vars->attempts)): ?>
<div id="boss_collapse">
<?php foreach($sidebar_vars->attempts as $name => &$attempts): ?>
<h3><?=$name?></h3>
<div>
	<div class="col">
<?php
	$half = ceil(sizeof($attempts)/2);
    foreach($attempts as $i => &$attempt):
		if($i == $half): ?>
	</div>
    <div class="col">
		<?php endif; ?>
    	<a href="<?=base_url()?>report/view/<?=$log_id?>/<?=$attempt->start?>/<?=$attempt->end?>/">Attempt <?=$i+1?> (<?=seconds_to_minutes($attempt->length)?>)<span class="icon <?=$attempt->wipe ? 'cross' : 'check'?>"></span></a>
	<?php endforeach; ?>
    </div>
    <div class="clear"></div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p>It appears your log does not contain any valuable boss data. If your log contained data from bosses in 10 and 20 player raids and shows this, please submit a report on the contact page!</p>
<?php endif; ?>
</div>