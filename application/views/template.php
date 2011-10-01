<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>RaidRifts - Rift Combat Log Analysis</title>
<meta name="description" content="RaidRifts is a combat log analysis tool for Rift, which helps players analyze the performance in damage, healing, and other aspects of raid combat." />
<meta name="keywords" content="RaidRifts, analysis, trion, raid, guild, damage, healing, rift, combat log, pve, dungeons" />
<link rel="shortcut icon" href="<?=base_url()?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/style.css?3" />
<script type="text/javascript">
var path='<?=base_url()?>';
</script>
<?php if(IS_LOCAL_TEST): ?>
<script type="text/javascript" src="<?=base_url()?>js/raidrifts.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?=base_url()?>js/raidrifts.min.js"></script>
<script type="text/javascript">var _gaq =_gaq||[];_gaq.push(['_setAccount','UA-24111550-1']);_gaq.push(['_trackPageview']);(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();</script>
<?php endif; ?>
<script type="text/javascript" src="<?=base_url()?>js/excanvas.min.js"></script>
</head>
<body>
<div id="header">
<div id="header-wrap">
<p class="logo"><a href="<?=base_url()?>">RaidRifts</a></p>
<p class="catchphrase">Rift logs <span class="glow">made easy.</span></p>
<?php if($this->user->id): ?>
<?php if($this->user->guild > 0 && $this->user->has_permission(ACCESS_LOG_UPLOAD)): ?>
    <a href="<?=base_url()?>downloads/client/rr_client.jar" id="client_download">
        <span class="version">v1.0 English</span>
    </a>
<?php endif; ?>
	<p class="user">You are logged in as <span class="glow"><?=$this->user->username?></span></p>
	<ul class="mini-navigation">
<? if($this->user->guild > 0): ?>
		<li><a href="<?=base_url()?>browse/guild/<?=$this->user->guild?>/">Guild Calendar</a></li>
<? endif; ?>
		<li><a href="<?=base_url()?>members/controlpanel/">Control Panel</a></li>
		<li><a href="<?=base_url()?>login/">Log Out</a></li>
	</ul>
<?php else: ?>
	<p class="login">Please <a href="#" id="login_link">log in</a> or <a href="#" id="register_link">register</a> to upload logs.</p>
<?php endif; ?>
<ul class="navigation">
	<li><a href="<?=base_url()?>">Home</a></li>
	<li><a href="<?=base_url()?>browse/">Browse</a></li>
	<li><a href="<?=base_url()?>page/about/">About</a></li>
<? if($this->user->admin): ?>
	<li><a href="<?=base_url()?>forums/">Forums</a></li>
<? endif; ?>
	<li><a href="<?=base_url()?>page/contact/">Contact</a></li>
</ul>
</div>
</div>
<div id="postnav">
<noscript>
<div class="alert"><p>It appears you do not have javascript enabled. This site makes extensive use of javascript.</p></div>
</noscript>
</div>
<div id="container1">
<div id="container2">
<div id="lsb">
<?php
if(isset($sidebar)):
	$this->load->view('sidebar/'.$sidebar);
else:
	$this->db->select('logs.hash, logs.uploaded, guilds.name');
	$this->db->join('guilds', 'guilds.id = logs.gid');
	$this->db->where(array('logs.processed' => 1, 'logs.private' => 0));
	$this->db->order_by('logs.uploaded', 'desc');
	$this->db->limit(10);
	$rs = $this->db->get('logs');
?>
<h2 class="sidebarheading">RaidRifts Information</h2>
<div class="content-block">
<h2>Latest Public Logs</h2>
<p>
<? foreach($rs->result() as $row): ?>
	<a href="<?=base_url()?>report/view/<?=$row->hash?>/"><?=$row->name?></a> - <?=time_ago(strtotime($row->uploaded), true)?> ago.<br />
<? endforeach; ?>
</p>
</div>
<?php
endif;
?>
<div class="content-block">
<h2>Support RaidRifts</h2>
<p>While RaidRifts is a free service, it is not free to run and maintain the site. Any help you can offer to support the development of RaidRifts will be used to keep us afloat and providing regular updates. Thank you!</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="LHBC84TYWB7KA">
<p class="center">
	<input type="image" src="http://www.raidrifts.com/images/donate.png" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</p>
</form>
</div>
</div>
<div id="cb">
<?php
if(isset($body))
	$this->load->view($body);
elseif(isset($content))
	echo $content;
?>
<div class="clear"></div>
</div>
<div class="clear"></div>
</div>
</div>
<div id="footer">
<p>Copyright &copy; 2011 RaidRifts. All rights reserved.<br /><span>Page generated in {elapsed_time} seconds by <a href="http://codeigniter.com" target="_blank">CodeIgniter</a>.</span></p>
</div>
<?php if($this->user->id === false): ?>
<div id="login_box">
<form id="login_form" method="post" action="<?=base_url()?>login/">
<p>
	Username:<br />
	<input type="text" name="username" />
</p>
<p>
	Password: <a href="#" id="forgot_password" class="glow">(Forgot password)</a><br />
	<input type="password" name="password" />
</p>
<p>
	<a class="btn submit" href="#"><span>Log In</span></a>
</p>
<p style="display:none">
	Email used to register:<br />
	<input type="text" name="email" />
</p>
<p style="display:none">
	<a class="btn submit" href="#"><span>Send New Password</span></a><a class="btn reset" href="#" onclick="$(this).parents('form').children('p').toggle()" style="margin-left:5px"><span>Cancel</span></a>
</p>
</form>
</div>
<div id="register_box">
<form id="register_form" method="post" action="<?=base_url()?>register/">
<p>
	Username*:<br />
	<input type="text" name="username" />
</p>
<p>
	Password*:<br />
	<input type="password" name="password1" />
</p>
<p>
	Password Confirm*:<br />
	<input type="password" name="password2" />
</p>
<p>
	E-mail*:<br />
	<input type="text" name="email" />
</p>
<p class="right"><a class="btn submit float-left" href="#"><span>Submit</span></a>* - Required field.</p>

</form>
</div>
<?php endif; ?>
<div id="qtip-blanket"></div>
</body>
</html>