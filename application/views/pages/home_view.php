<h1 class="componentheading">
<?php if($this->user->admin): ?>
	<a class="btn float-right" href="<?=base_url()?>page/edit/"><span>Edit Page</span></a>
<?php endif; ?>
	Welcome to RaidRifts!
</h1>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 8/17/2011</h2>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed an issue with the parser not ending fights that have dots still ticking on players.</li>
    <li>Fixed another issue with the parser regarding multi-phased encounters.</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>Guild masters can now specify their default log privacy. You can still set logs to be public/private manually.</li>
	<li>You can now apply for a guild in the guild control panel. It'll be next to the guild creation box.</li>
	<li>There is now a guild calendar link when you are logged in and in a guild. No more hunting through the browse section!</li>
    <li>Omg! New death and raid cooldown markers are on the graph! They can be toggled on and off with the skull and star icon to the bottom right of the graph.</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 8/5/2011</h2>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed issue where users recently invited to a guild couldn't see private logs.</li>
	<li>Fixed an issue with newly created guilds not showing the name under guild settings.</li>
	<li>Some information was being incorrectly cached within a user's session causing bugs related to their guild. This has been corrected with some new nerdy goodness in the backend for pinpoint accuracy.</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>You can now promote users to officer rank to upload logs, accept applications, and kick users. Only the guild master will be able to promote or demote users.</li>
	<li>You may now leave your guild if you are not the guild leader. If you are the guild leader, you must be the only member or pass leadership in order to leave the guild. There is no permanent way to delete a guild for tracking reasons, please use the contact form if you would like one explicitly deleted.</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 8/2/2011</h2>
<p>The public beta is finally here! Register away, nerds.</p>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed an issue with the client involving corrupted logs (for real this time).</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>There are now guild settings. Guild masters can now change their guild name, server, faction, and add their website. A way to display some of these details will be added soon.</li>
	<li>People can now apply to your guild through your guild page in the "browse" section. This is how you will invite members to view private logs.</li>
	<li>There is now a visible member list that will show the user's rank, name, date they joined, and for guild administrators, the ability to kick them. The promote/demote system will be in shortly.</li>
	<li>You can now set the date the log took place in the backend.</li>
	<li>The browse system is now implemented. You will first see a list of shards, where you can find a list of guilds within that shard. Once you click that guild you can see a calendar with public logs they have. If you wish to apply to the guild you can find a button on the calendar as well.</li>
	<li>There is now a nifty sidebar that shows 10 of the most recent public logs uploaded.</li>
</ul></li>
<li><b>Next week's features/fixes</b><ul>
	<li>Promote/demote users.</li>
	<li>Possible recruitment message?</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 7/25/2011</h2>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed an issue with the client involving corrupted logs.</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>There's now a damage taken section on the player breakdown. For tanks, if you wish to view percentages for avoidance, mouse over any ability that does not have 100% hits.</li>
	<li>You can now change your password in the user backend. Kindof a big deal.</li>
</ul></li>
<li><b>Next week's features/fixes</b><ul>
	<li>Public beta!</li>
	<li>Ability to invite users to your guild to view private logs.</li>
    <li>Ability to give users access to upload logs.</li>
    <li>Shard and guild browser.</li>
    <li>Public guild calendar.</li>
    <li>Date field for logs (goes with above calendar).</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 7/18/2011</h2>
<p>I figure it would be better to make weekly updates to ease the amount of reading, so I'll just keep doing this.</p>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed bugs with multi-phase encounters having split data.</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>There's a new damage taken tab that includes graph data. Player breakdown to come soon.</li>
</ul></li>
<li><b>Performance Enhancements</b><ul>
	<li>Faster page load times (200% faster, just a bit).</li>
    <li>Increased performance for rendering encounter data (30% faster page loads for large encounters).</li>
</ul></li>
<li><b>Upcoming Features/Fixes</b><ul>
	<li>Player breakdown for damage taken.</li>
    <li>Log calendar for public display.</li>
    <li>Date field for logs (goes with above calendar).</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 7/11/2011</h2>
<p>Sorry for the long time since an update! I'm working hard on the site in my spare time; business has started picking up quite over the last few weeks, but will finally start to settle out over this next week. I put in some new features for the time being that should help push forward for a little bit.</p>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed a few bugs for various encounters, too many to list!</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>Added the ability to delete logs.</li>
</ul></li>
<li><b>Upcoming Features/Fixes</b><ul>
	<li>Better multi-phase boss support.</li>
	<li>More backend guild controls, particularly a rank system.</li>
    <li>A lot of logs are getting corrupted during the upload process. I'm currently working on a permanent fix.</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 7/3/2011</h2>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed an issue with air phases being split up on the Alsbeth the Discordant encounter.</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 7/2/2011</h2>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed various IE7 style bugs</li>
    <li>Fixed IE being unable to submit forms by pressing enter.</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>The user control panel now has the ability to edit logs. You can now set them to be visible to the public or not, and add notes so you can identify their purpose more easily.</li>
</ul></li>
</ul>
</div>

<div class="content-block">
<h2 class="content-heading chat">Site Updates 6/30/2011</h2>
<p>Time for a status report on what's going on!</p>
<ul>
<li><b>Bugfixes</b><ul>
	<li>Fixed an issue where your username can be too long. Whoops!</li>
    <li>Fixed account authentication inconsistency between client and website.</li>
    <li>Fixed a JavaScript error when viewing certain logs.</li>
    <li>Fixed my horrible spelling on the client.</li>
    <li>Filtered out Shocking Cipher (Plutonus the Immortal) and Deathly Flames (Lord Greenscale).</li>
</ul></li>
<li><b>New Features</b><ul>
	<li>The registration feature is now active. You need a key to use it.</li>
	<li>There is now a much easier way to add a guild in the user control panel.</li>
</ul></li>
</ul>
</div>