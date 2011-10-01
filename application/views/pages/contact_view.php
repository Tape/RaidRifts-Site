<h1 class="componentheading">Contact Us</h1>
<div class="content-block float-left small">
<h2 class="content-heading note">Contact Form</h2>
<form method="post" action="<?=base_url()?>page/contact/" id="contact">
<div class="msg"></div>
<p>
	Reason:<br />
	<select name="reason">
    	<option>General feedback</option>
    	<option>Feature request</option>
    	<option>Guild name claimed</option>
    	<option>Bug report</option>
    	<option>Misinformation</option>
    	<option>Typo/Spelling</option>
    	<option>Partnership</option>
    	<option>Other</option>
    </select>
</p>
<p>
	Page URL (optional):<br />
	<input type="text" name="url" />
</p>
<p>
	Contact Email (only if you want a response):<br />
	<input type="text" name="email" />
</p>
<p>
	Message (please be specific!):<br />
	<textarea name="message" rows="7" cols="41"></textarea>
</p>
<div style="display:none"><textarea name="comments" rows="7" cols="41"></textarea></div>
<p>
	<a class="btn submit" href="#"><span>Submit</span></a>
</p>
</form>
</div>
<div class="content-block float-right" style="width:280px">
<h2 class="content-heading chat">Thanks!</h2>
<p>Thank you for deciding to reach out to us. We always read all of our emails and will try to reply within <span class="glow">24 hours</span>.</p>
<p>We will always take any suggestions into consideration, and feedback always helps us to improve RaidRifts to become the best logging tool out there.</p>
<p>Thank you again for your support!</p>
</div>