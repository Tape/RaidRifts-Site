<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>RaidRifts - Rift Combat Log Analysis</title>
<meta name="description" content="RaidRifts is a combat log analysis tool for Rift, which helps players analyze the performance in damage, healing, and other aspects of raid combat." />
<meta name="keywords" content="RaidRifts, analysis, trion, raid, guild, damage, healing, rift, combat log, pve, dungeons" />
<link rel="shortcut icon" href="<?=base_url()?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/style.css?3" />
<script type="text/javascript" src="<?=base_url()?>js/raidrifts.min.js?2"></script>
<script type="text/javascript" src="<?=base_url()?>js/excanvas.min.js"></script>
<?php if(IS_LOCAL_TEST): ?>
<script type="text/javascript">
function toggle_markings(e, type, id)
{
	if(id.length) {
		for(i = 0; i < id.length; i++) window.plots[type].t[id[i]] = !window.plots[type].t[id[i]];
	} else {
		window.plots[type].t[id] = !window.plots[type].t[id];
	}
	reGraph(type);
}

function load_guilds(id)
{
	var box = $('#apply_wrapper');
	$('#apply_controls').hide();
	box.find('select').selectBox('destroy');
	box.find('div').empty();
	box.find('span').text('Loading guilds...');
	
	$.ajax({
		url: '<?=base_url()?>members/ajax/guilds/',
		type: 'POST',
		data: {id: id},
		dataType: 'json',
		success: function(response) {
			//Find the box and empty text.
			box.find('span').empty();
			
			//If our response does not contain guilds.
			if(response.error) {
				box.find('span').text(response.error);
				return;
			}
			
			//If our response does contain guilds then make a select box.
			var sel = $(document.createElement('select')).attr({
				name: 'gid'
			}).change(function() {
				if($(this).val() > 0)
					$('#apply_controls').show();
			}).appendTo(box.find('div'));
			
			//Loop through each guild.
			for(i = 0; i < response.guilds.length; i++) {
				sel.append($(document.createElement('option')).val(response.guilds[i].id).text(response.guilds[i].name));
			}
			
			//Make the select box look pretty now.
			sel.selectBox();
		}
	});
}

function reGraph(type)
{
	var data = [], markings = [];
	$('input[type=checkbox][rel='+type+']:checked').each(function() {
		var name = $(this).attr('name');
		if(g[type][name]) data.push(g[type][name]);
	});
	$.each(m, function(i, val) {
		if(!window.plots || !window.plots[type] || window.plots[type].t[val.i]) markings.push({
			color: val.c,
			lineWidth: 1,
			xaxis: { from: val.t, to: val.t + val.l }
		});
	});
	if(!window.plots) window.plots = {};
	var plot = $.plot($('.graph_canvas[rel='+type+']'), data, {
		legend: {
			backgroundColor: 'transparent',
			container: $('.graph_legend[rel='+type+']'),
			noColumns: 8
		},
		yaxes: [{ min: 0 },
				{
					alignTicksWithAxis: 1,
					position:"right"
				}],
		grid: {
			markings: markings,
			hoverable: true
		}
	});
	
	//Store the plot and get the distance of data 5 pixels away.
	plot.distance = plot.getAxes().xaxis.datamax / plot.width() * 5;
	if(window.plots[type]) {
		plot.t = window.plots[type].t;
		window.plots[type] = plot;
	} else {
		plot.t = {
			0: true,
			1: true,
			2: true
		};
		window.plots[type] = plot;
	}
}

function notify(text, error)
{
	var e = $(document.createElement('div')).attr('class', error ? 'alert' : 'notification');
	return e.append($(document.createElement('p')).text(text)).hide();
}

function checkAll(type, e)
{
	$('input[type=checkbox][rel='+type+']').attr('checked', !$(e).attr('checked') ? false : true);
	checks(type);
}

function checks(type)
{
	if(typeof type == 'object') type = $(this).attr('rel');
	var opt = null;
	if($('input[type=checkbox][rel='+type+']:checked').length < 1) {
		opt = {disabled:true,checked:true};
	} else {
		opt = {disabled:false};
	}
	$('input[rel='+type+'][name=total]').attr(opt);
	reGraph(type);
}

function deleteLog()
{
	if(confirm("Are you sure you want to delete this log?")) {
		var e = $(this), data = {
			id: e.attr('rel')
		};
		$.post('<?=base_url()?>members/ajax/deletelog/', data, function(data) {
			var n;
			if(data == 'SUCCESS') {
				n = notify("Your log has been removed successfully.");
				var tr = e.parents('tr').remove();
			} else {
				n = notify("An error occurred while removing your log.", true);
			}
			$('.inner.logs').prepend(n);
			n.rrSlideIn();
		});
	}
}

function modal(title, contents)
{
	return {
		content: {
			title: {
				text: title,
				button: 'Close'
			},
			text: contents
		},
		position: {
			target: $(document.body),
			corner: 'center'
		},
		show: {
			when: 'click',
			solo: true
		},
		hide: false,
		style: {
			name: 'dark'
		},
		api: {
			beforeShow:function() {
				$('#qtip-blanket').fadeIn(this.options.show.effect.length);
			},
			beforeHide:function() {
				$('#qtip-blanket').fadeOut(this.options.show.effect.length);
			}
		}
	};
}

$(function() {
	var tab_count = 3,
	names=[];
	$('.alert,.notification').live('blur', function() {
		rel = parseInt($(this).attr('rel'));
		if(rel != 0) $(this).rrSlideOut(rel*1000);
	}).trigger('blur');
	$('select').selectBox();
	$('.sorter').tablesorter();
	$('.sortable').each(function() {
		var type = $(this).attr('rel');
		$(this).children('tbody').children('tr').each(function(index) {
			//Make the hyperlink open a new tab.
			$(this).find('a').click(function() {
				var name = $(this).html();
				if(names.indexOf(name) == -1) {
					$tabs.tabs("add", '#tabs-'+(++tab_count), name);
					names.push(name);
				}
				return false;
			});

			//Empty result, move on!
			var actor = t[type][index];
			if(actor.values.length == 0) return true;

			//Build up tooltip content.
			var $div = $('<div />');
			var run = false;
			while(true) {
				//Run once at the least.
				if(!run) {
					run = true;
				//Actor has a pet.
				} else if(run && actor.pet) {
					actor = actor.pet;
				//Actor has no pet or pet has already been reported.
				} else {
					break;
				}

				//Append the new elements to the table.
				var $table = $('<table cellspacing="0" class="data_table" />');
				$div.append("<h4>"+actor.name+"</h4>", $table);
				$table.append('<tr><th width="150" align="center">Spell</th><th colspan="3" align="center">Amount</th></tr>');
				for(i = 0; i < actor.values.length; i++) {
					var percent = actor.values[i][1]/actor.total*100,
						$row = $('<tr />'), $bar = $('<div />');
					$bar.progressbar({value:Math.round(actor.values[i][1]/actor.values[0][1]*100)});
					$row.append('<td>'+actor.values[i][0]+'</td><td align="right" width="50">'+actor.values[i][1]+'</td>');
					$row.append($bar);
					$bar.wrap('<td width="120" />');
					$row.append('<td align="right" width="50">'+percent.toFixed(1)+'%</td>');
					$table.append($row);
				}
			}

			$(this).qtip({
				content: $div,
				position: {
					target: 'mouse',
					corner: {
						target: 'topLeft',
						tooltip: 'bottomRight'
					}
				},
				show: {
					delay: 0,
					effect: {
						length: 0
					}
				},
				hide: {
					delay: 0,
					effect: {
						length: 0
					}
				},
				style: {
					name: 'dark'
				}
			});
		});
	});
	
	$('input,textarea').keydown(function(e) {
		var elem = $(this), form = elem.parents('form');
		if(e.keyCode == 9) {
			var inputs = form.find('input:visible,textarea:visible'), num = inputs.index(elem)+1;
			inputs.eq(num >= inputs.length ? 0 : num).focus();
			return false;
		}
		if(e.keyCode == 13 && !elem.is('textarea')) {
			form.submit();
			return false;
		}
	});
	
	$('.btn.submit').click(function() {
		var rel = $(this).attr('rel'), run = false;
		if(rel) {
			if(confirm(rel)) run = true;
		} else run = true;
		if(run) $(this).parents('form').submit();
		return false;
	});
	
	$('.btn.reset').click(function() {
		$(this).parents('form')[0].reset();
		return false;
	});

	$('.edit').click(function() {
		$('#save_changes').show();
		var tds = $(this).parents('tr').children('td'), id = $(this).attr('rel');
		$(this).remove();
		var public = $(tds[2]).children('span');
		public.replaceWith($(document.createElement('input')).attr({
			type: 'checkbox',
			checked: public.hasClass('check'),
			name: 'logs['+id+'][public]'
		})).remove();
		var notes = $(tds[3]), text = notes.text();
		notes.attr('align', 'center').empty().append($(document.createElement('input')).attr({
			type: 'text',
			value: text,
			name: 'logs['+id+'][notes]'
		}).css({width:'250px'}));
		var date = $(tds[4]), text = date.text();
		date.attr('align', 'center').empty().append($(document.createElement('input')).attr({
			type: 'text',
			value: text,
			name: 'logs['+id+'][date]'
		}).css({width:'75px'}).datepicker({showOptions:{direction:'up'}}));
	});

	$('.delete').click(deleteLog);

	$('.sortable').tablesorter({
		widgets: ['zebra'],
		sortList: [[2,1]],
		headers: {
			0: {sorter:false},
			2: {dir:1},
			3: {dir:1},
			4: {sorter:false},
			5: {dir:1}
		}
	});

	$('#boss_collapse').accordion({
		collapsible:true,
		autoHeight:false,
		active:false,
		change:function() {
			$(this).find('h3').blur();
		}
	});

	$('.percent_visual').each(function() {
		var val = Number($(this).attr('rel'));
		$(this).progressbar({value:val});
	});

	$('.graph_canvas').each(function() {
		//Grab the type.
		var type = $(this).attr('rel');
		reGraph(type);
		$(this).bind('plothover', function(event, pos, item) {
			//Remove the tooltip.
			var e = $('#tooltip_graph'), markings = [], plot = window.plots[$(this).attr('rel')];
			//Search for markings nearby.
			var distance = plot.distance, offset = plot.offset(), height = plot.height(), width = plot.width();
			var inbounds = pos.pageY >= offset.top && pos.pageY <= offset.top + height && pos.pageX >= offset.left && pos.pageX <= offset.left + width;
			for(i = 0; i < m.length; i++) {
				if(plot.t[m[i].i] && inbounds) switch(true) {
					case Math.abs(m[i].t - pos.x) <= distance:
					case Math.abs(m[i].t + m[i].l - pos.x) <= distance:
					case m[i].t <= pos.x && m[i].t + m[i].l >= pos.x:
					markings.push(m[i]);
					break;
				}
			}
			
			//Check if we have a valid tooltip.
			if(item || markings.length > 0) {
				//Make the tooltip.
				if(e.length == 0) e = $(document.createElement('div')).attr('id', 'tooltip_graph').appendTo('body');
				e.css({
					top: pos.pageY + 5,
					left: pos.pageX + 5
				});
				//Build the text.
				var text = [];
				if(markings.length > 0) for(i = 0; i < markings.length; i++) {
					var marktype = markings[i].i, str;
					switch(marktype) {
						case 0: str = ' is killed by ';break;
						case 1: str = ' is revived by ';break;
						case 2: str = ' casts ';break;
					}
					text.push(markings[i].t + 's, ' + markings[i].n + str + markings[i].o);
				}
				if(item) text.push(item.series.label + ': ' + item.datapoint[0] + 's, ' + item.datapoint[1] + (type == 'healing' ? ' HPS' : ' DPS'));
				//Join the text.
				e.html(text.join('<br />'));
			} else e.remove();
		});
	});

	$('#qtip-blanket').css({
		top:$(document).scrollTop(),
		height:$(document).height(),
		opacity:0.7
	});
	
	var login_form = $('#login_box').detach().show();
	//Change the layout of the login box should they click the "forgot password" link.
	login_form.find('#forgot_password').click(function() {
		$(this).parents('form').children('p').toggle();
		return false;
	});
	$('#login_link').qtip(modal('Please Log In', login_form));
	
	var apply_form = $('#apply_box').detach().show();
	$('#apply_link').qtip(modal('Apply to this guild', apply_form));
	
	register_form = $('#register_box').detach().show();
	$('#register_link').qtip(modal('Register with RaidRifts', register_form));

	var login_check = true;
	login_form.find('form').submit(function() {
		var o = $(this);
		if(login_check) {
			login_check = false;
			o.prepend('<p class="notification">Please wait...</p>');
			$.post(o.attr('action'), o.serialize(), function(data) {
				o.find('.notification').remove();
				var e = $(document.createElement('p')).hide();
				if(data == 'SUCCESS' && o.find('input[name=email]').val() == '') {
					e.attr('class', 'notification').text('Logged in! Redirecting...');
					location.reload(true);
				} else if(data == 'SUCCESS') {
					login_check = true;
					e.attr('class', 'notification').text('Check your email for new password');
					o[0].reset();
					o.find('p').toggle();
				} else {
					login_check = true;
					e.attr('class', 'alert').text(data);
				}
				$('#login_box').prepend(e);
				e.rrSlideIn();
			});
		}
		return false;
	});

	var apply_check = true;
	apply_form.find('form').submit(function() {
		var o = $(this);
		if(apply_check) {
			var box = $('#apply_box');
			box.find('.alert,.notification').remove();
			box.prepend('<p class="notification">Submitting...</p>');
			apply_check = false;
			$.post(o.attr('action'), o.serialize(), function(data) {
				box.find('.notification,.alert').remove();
				if(data == 'SUCCESS') {
					box.prepend('<p class="notification">Your application has been submitted!</p>');
				} else {
					apply_check = true;
					box.prepend('<p class="alert">An error occurred: '+data+'.</p>');
				}
			});
		}
		return false;
	});

	var register_check = true;
	register_form.find('form').submit(function() {
		var o = $(this);
		if(register_check) {
			var box = $('#register_box');
			box.find('.alert,.notification').remove();
			box.prepend('<p class="notification">Submitting...</p>');
			register_check = false;
			$.post(o.attr('action'), o.serialize(), function(data) {
				if(data == 'SUCCESS') {
					window.location = '<?=base_url()?>register/complete/';
				} else {
					register_check = true;
					box.find('.alert,.notification').remove();
					box.prepend($('<p class="alert">'+data+'</p>'));
				}
			});
		}
		return false;
	});
	
	$('input[type=checkbox][rel]').click(checks);
	
	$('.applications tr').each(function() {
		var rel = $(this).attr('rel');
		if(rel) {
			$(this).qtip({
				content: rel,
				style: {name:'dark'},
				position: {
					corner: {
						target: 'topMiddle',
						tooltip: 'bottomMiddle'
					}
				}
			});
		}
	});
	
	$('.applications .check,.applications .cross').click(function() {
		var e = $(this), type = e.hasClass('cross') ? 'decline' : 'accept', tr = e.parents('tr');
		if(confirm('Are you sure you want to '+type+ ' this application?')) {
			$.post('<?=base_url()?>members/ajax/application/', {
				aid: tr.attr('ajax'),
				action: type
			}, function(data) {
				if(data == 'SUCCESS') {
					if(tr.attr('rel'))
						tr.qtip('destroy');
					tr.remove();
				} else {
					notify("An error occurred while processing your request.", true).prependTo($('.inner.guild_settings')).rrSlideIn();
				}
			});
		}
		return false;
	});
	
	var ranks = [
		[1, 'Member'],
		[63, 'Officer']
	], promotion = {};
	$('.member_list .plus, .member_list .minus').click(function() {
		$('#member_changes').show();
		var e = $(this), val = e.hasClass('plus'), tr = e.parents('tr'), other = tr.find('.'+(val ? 'minus' : 'plus')), text = e.parent().children('span'), id = tr.attr('rel');
		e.hide();
		other.show();
		if(val) {
			text.text(ranks[1][1]);
			promotion[id] = ranks[1][0];
		} else {
			text.text(ranks[0][1]);
			promotion[id] = ranks[0][0];
		}
		
		return false;
	});
	
	$('.member_list .cross').click(function() {
		var e = $(this), tr = e.parents('tr');
		if(confirm('Are you sure you want to remove this member from the guild?')) {
			$.post('<?=base_url()?>members/ajax/kick/', {
				uid: tr.attr('rel')
			}, function(data) {
				var n;
				if(data == 'SUCCESS') {
					n = notify("The user you have selected was removed.");
					var tr = e.parents('tr').remove();
				} else {
					n = notify("An error occurred while removing your log.", true);
				}
				$('.inner.guild_settings').prepend(n);
				n.rrSlideIn();
			});
		}
		return false;
	});
	
	$('.member_list form').submit(function() {
		var tmp = [];
		for(id in promotion) tmp.push(id+':'+promotion[id]);
		
		$(this).append($(document.createElement('input')).attr({
			type: 'hidden',
			name: 'changes',
			value: tmp.join(';')
		}).show());
	});
	
	var $tabs = $('#tabs').tabs({
		tabTemplate:'<li><a href="#{href}" class="ui-closable">#{label}</a><span class="icon ui-icon-close"></span></li',
		navigation:true,
		add:function(e, ui) {
			$(ui.panel).html("<p>Please wait while your data is being grabbed...</p>");
			v.n = ui.tab.innerHTML;
			$.ajax({
				type:"GET",
				url:'<?=base_url()?>report/tab/',
				data:v,
				success:function(msg) {
					$(ui.panel).html(msg);
				}
			});
		}
	});

	$(".ui-icon-close").live("click", function() {
		var name = $(this).parent().children('a').html();
		var index = $("li", $tabs).index($(this).parent());
		$tabs.tabs("remove", index);
		names.deleteElement(name);
	});
	
	$('.shrinkable').each(function() {
		$(this).click(function() {
			var e = $(this).parent().parent().children('.inner');
			if($(this).hasClass('expand')) {
				$(this).addClass('shrink').removeClass('expand');
				e.slideDown(500);
			} else {
				$(this).addClass('expand').removeClass('shrink');
				e.slideUp(500);
			}
		});
	});
	
	var timeout = true;
	$('#contact').submit(function() {
		if(timeout) {
			timeout = false;
			var o = $(this);
			o.find('.msg').attr('class', 'msg').show(500).text("Submitting your feedback...");
			$.post(o.attr('action'), o.serialize(), function(data) {
				if(data == 'SUCCESS') {
					o.find('.msg').text('Your feedback has been sent, thanks!').addClass('success').remove('error');
				} else {
					o.find('.msg').text('Please fill out the message field below.').addClass('error').remove('success');
				}
				timeout = true;
			});
		}
		return false;
	});
});
</script>
<?php else: ?>
<script type="text/javascript">
function toggle_markings(f,d,c){if(c.length){for(i=0;i<c.length;i++){window.plots[d].t[c[i]]=!window.plots[d].t[c[i]]}}else{window.plots[d].t[c]=!window.plots[d].t[c]}reGraph(d)}function load_guilds(b){var a=$("#apply_wrapper");$("#apply_controls").hide();a.find("select").selectBox("destroy");a.find("div").empty();a.find("span").text("Loading guilds...");$.ajax({url:"<?=base_url()?>members/ajax/guilds/",type:"POST",data:{id:b},dataType:"json",success:function(d){a.find("span").empty();if(d.error){a.find("span").text(d.error);return}var c=$(document.createElement("select")).attr({name:"gid"}).change(function(){if($(this).val()>0){$("#apply_controls").show()}}).appendTo(a.find("div"));for(i=0;i<d.guilds.length;i++){c.append($(document.createElement("option")).val(d.guilds[i].id).text(d.guilds[i].name))}c.selectBox()}})}function reGraph(e){var h=[],a=[];$("input[type=checkbox][rel="+e+"]:checked").each(function(){var b=$(this).attr("name");if(g[e][b]){h.push(g[e][b])}});$.each(m,function(c,b){if(!window.plots||!window.plots[e]||window.plots[e].t[b.i]){a.push({color:b.c,lineWidth:1,xaxis:{from:b.t,to:b.t+b.l}})}});if(!window.plots){window.plots={}}var f=$.plot($(".graph_canvas[rel="+e+"]"),h,{legend:{backgroundColor:"transparent",container:$(".graph_legend[rel="+e+"]"),noColumns:8},yaxes:[{min:0},{alignTicksWithAxis:1,position:"right"}],grid:{markings:a,hoverable:true}});f.distance=f.getAxes().xaxis.datamax/f.width()*5;if(window.plots[e]){f.t=window.plots[e].t;window.plots[e]=f}else{f.t={0:true,1:true,2:true};window.plots[e]=f}}function notify(d,c){var f=$(document.createElement("div")).attr("class",c?"alert":"notification");return f.append($(document.createElement("p")).text(d)).hide()}function checkAll(b,c){$("input[type=checkbox][rel="+b+"]").attr("checked",!$(c).attr("checked")?false:true);checks(b)}function checks(d){if(typeof d=="object"){d=$(this).attr("rel")}var c=null;if($("input[type=checkbox][rel="+d+"]:checked").length<1){c={disabled:true,checked:true}}else{c={disabled:false}}$("input[rel="+d+"][name=total]").attr(c);reGraph(d)}function deleteLog(){if(confirm("Are you sure you want to delete this log?")){var b=$(this),a={id:b.attr("rel")};$.post("<?=base_url()?>members/ajax/deletelog/",a,function(d){var e;if(d=="SUCCESS"){e=notify("Your log has been removed successfully.");var c=b.parents("tr").remove()}else{e=notify("An error occurred while removing your log.",true)}$(".inner.logs").prepend(e);e.rrSlideIn()})}}function modal(d,c){return{content:{title:{text:d,button:"Close"},text:c},position:{target:$(document.body),corner:"center"},show:{when:"click",solo:true},hide:false,style:{name:"dark"},api:{beforeShow:function(){$("#qtip-blanket").fadeIn(this.options.show.effect.length)},beforeHide:function(){$("#qtip-blanket").fadeOut(this.options.show.effect.length)}}}}$(function(){var f=3,h=[];$(".alert,.notification").live("blur",function(){if($(this).attr("rel")!=0){$(this).rrSlideOut()}}).trigger("blur");$("select").selectBox();$(".sorter").tablesorter();$(".sortable").each(function(){var k=$(this).attr("rel");$(this).children("tbody").children("tr").each(function(p){$(this).find("a").click(function(){var z=$(this).html();if(h.indexOf(z)==-1){n.tabs("add","#tabs-"+(++f),z);h.push(z)}return false});var x=t[k][p];if(x.values.length==0){return true}var w=$("<div />");var u=false;while(true){if(!u){u=true}else{if(u&&x.pet){x=x.pet}else{break}}var r=$('<table cellspacing="0" class="data_table" />');w.append("<h4>"+x.name+"</h4>",r);r.append('<tr><th width="150" align="center">Spell</th><th colspan="3" align="center">Amount</th></tr>');for(i=0;i<x.values.length;i++){var q=x.values[i][1]/x.total*100,l=$("<tr />"),s=$("<div />");s.progressbar({value:Math.round(x.values[i][1]/x.values[0][1]*100)});l.append("<td>"+x.values[i][0]+'</td><td align="right" width="50">'+x.values[i][1]+"</td>");l.append(s);s.wrap('<td width="120" />');l.append('<td align="right" width="50">'+q.toFixed(1)+"%</td>");r.append(l)}}$(this).qtip({content:w,position:{target:"mouse",corner:{target:"topLeft",tooltip:"bottomRight"}},show:{delay:0,effect:{length:0}},hide:{delay:0,effect:{length:0}},style:{name:"dark"}})})});$("input,textarea").keydown(function(r){var l=$(this),q=l.parents("form");if(r.keyCode==9){var k=q.find("input:visible,textarea:visible"),p=k.index(l)+1;k.eq(p>=k.length?0:p).focus();return false}if(r.keyCode==13&&!l.is("textarea")){q.submit();return false}});$(".btn.submit").click(function(){var k=$(this).attr("rel"),l=false;if(k){if(confirm(k)){l=true}}else{l=true}if(l){$(this).parents("form").submit()}return false});$(".btn.reset").click(function(){$(this).parents("form")[0].reset();return false});$(".edit").click(function(){$("#save_changes").show();var l=$(this).parents("tr").children("td"),s=$(this).attr("rel");$(this).remove();var k=$(l[2]).children("span");k.replaceWith($(document.createElement("input")).attr({type:"checkbox",checked:k.hasClass("check"),name:"logs["+s+"][public]"})).remove();var r=$(l[3]),q=r.text();r.attr("align","center").empty().append($(document.createElement("input")).attr({type:"text",value:q,name:"logs["+s+"][notes]"}).css({width:"250px"}));var p=$(l[4]),q=p.text();p.attr("align","center").empty().append($(document.createElement("input")).attr({type:"text",value:q,name:"logs["+s+"][date]"}).css({width:"75px"}).datepicker({showOptions:{direction:"up"}}))});$(".delete").click(deleteLog);$(".sortable").tablesorter({widgets:["zebra"],sortList:[[2,1]],headers:{0:{sorter:false},2:{dir:1},3:{dir:1},4:{sorter:false},5:{dir:1}}});$("#boss_collapse").accordion({collapsible:true,autoHeight:false,active:false,change:function(){$(this).find("h3").blur()}});$(".percent_visual").each(function(){var k=Number($(this).attr("rel"));$(this).progressbar({value:k})});$(".graph_canvas").each(function(){var k=$(this).attr("rel");reGraph(k);$(this).bind("plothover",function(D,C,B){var w=$("#tooltip_graph"),s=[],x=window.plots[$(this).attr("rel")];var z=x.distance,p=x.offset(),E=x.height(),l=x.width();var u=C.pageY>=p.top&&C.pageY<=p.top+E&&C.pageX>=p.left&&C.pageX<=p.left+l;for(i=0;i<m.length;i++){if(x.t[m[i].i]&&u){switch(true){case Math.abs(m[i].t-C.x)<=z:case Math.abs(m[i].t+m[i].l-C.x)<=z:case m[i].t<=C.x&&m[i].t+m[i].l>=C.x:s.push(m[i]);break}}}if(B||s.length>0){if(w.length==0){w=$(document.createElement("div")).attr("id","tooltip_graph").appendTo("body")}w.css({top:C.pageY+5,left:C.pageX+5});var r=[];if(s.length>0){for(i=0;i<s.length;i++){var q=s[i].i,A;switch(q){case 0:A=" is killed by ";break;case 1:A=" is revived by ";break;case 2:A=" casts ";break}r.push(s[i].t+"s, "+s[i].n+A+s[i].o)}}if(B){r.push(B.series.label+": "+B.datapoint[0]+"s, "+B.datapoint[1]+(k=="healing"?" HPS":" DPS"))}w.html(r.join("<br />"))}else{w.remove()}})});$("#qtip-blanket").css({top:$(document).scrollTop(),height:$(document).height(),opacity:0.7});var e=$("#login_box").detach().show();e.find("#forgot_password").click(function(){$(this).parents("form").children("p").toggle();return false});$("#login_link").qtip(modal("Please Log In",e));var d=$("#apply_box").detach().show();$("#apply_link").qtip(modal("Apply to this guild",d));register_form=$("#register_box").detach().show();$("#register_link").qtip(modal("Register with RaidRifts",register_form));var b=true;e.find("form").submit(function(){var k=$(this);if(b){b=false;k.prepend('<p class="notification">Please wait...</p>');$.post(k.attr("action"),k.serialize(),function(l){k.find(".notification").remove();var p=$(document.createElement("p")).hide();if(l=="SUCCESS"&&k.find("input[name=email]").val()==""){p.attr("class","notification").text("Logged in! Redirecting...");location.reload(true)}else{if(l=="SUCCESS"){b=true;p.attr("class","notification").text("Check your email for new password");k[0].reset();k.find("p").toggle()}else{b=true;p.attr("class","alert").text(l)}}$("#login_box").prepend(p);p.rrSlideIn()})}return false});var a=true;d.find("form").submit(function(){var l=$(this);if(a){var k=$("#apply_box");k.find(".alert,.notification").remove();k.prepend('<p class="notification">Submitting...</p>');a=false;$.post(l.attr("action"),l.serialize(),function(p){k.find(".notification,.alert").remove();if(p=="SUCCESS"){k.prepend('<p class="notification">Your application has been submitted!</p>')}else{a=true;k.prepend('<p class="alert">An error occurred: '+p+".</p>")}})}return false});var y=true;register_form.find("form").submit(function(){var l=$(this);if(y){var k=$("#register_box");k.find(".alert,.notification").remove();k.prepend('<p class="notification">Submitting...</p>');y=false;$.post(l.attr("action"),l.serialize(),function(p){if(p=="SUCCESS"){window.location="<?=base_url()?>register/complete/"}else{y=true;k.find(".alert,.notification").remove();k.prepend($('<p class="alert">'+p+"</p>"))}})}return false});$("input[type=checkbox][rel]").click(checks);$(".applications tr").each(function(){var k=$(this).attr("rel");if(k){$(this).qtip({content:k,style:{name:"dark"},position:{corner:{target:"topMiddle",tooltip:"bottomMiddle"}}})}});$(".applications .check,.applications .cross").click(function(){var p=$(this),k=p.hasClass("cross")?"decline":"accept",l=p.parents("tr");if(confirm("Are you sure you want to "+k+" this application?")){$.post("<?=base_url()?>members/ajax/application/",{aid:l.attr("ajax"),action:k},function(q){if(q=="SUCCESS"){if(l.attr("rel")){l.qtip("destroy")}l.remove()}else{notify("An error occurred while processing your request.",true).prependTo($(".inner.guild_settings")).rrSlideIn()}})}return false});var o=[[1,"Member"],[63,"Officer"]],c={};$(".member_list .plus, .member_list .minus").click(function(){$("#member_changes").show();var p=$(this),r=p.hasClass("plus"),l=p.parents("tr"),k=l.find("."+(r?"minus":"plus")),q=p.parent().children("span"),s=l.attr("rel");p.hide();k.show();if(r){q.text(o[1][1]);c[s]=o[1][0]}else{q.text(o[0][1]);c[s]=o[0][0]}return false});$(".member_list .cross").click(function(){var l=$(this),k=l.parents("tr");if(confirm("Are you sure you want to remove this member from the guild?")){$.post("<?=base_url()?>members/ajax/kick/",{uid:k.attr("rel")},function(q){var r;if(q=="SUCCESS"){r=notify("The user you have selected was removed.");var p=l.parents("tr").remove()}else{r=notify("An error occurred while removing your log.",true)}$(".inner.guild_settings").prepend(r);r.rrSlideIn()})}return false});$(".member_list form").submit(function(){var k=[];for(id in c){k.push(id+":"+c[id])}$(this).append($(document.createElement("input")).attr({type:"hidden",name:"changes",value:k.join(";")}).show())});var n=$("#tabs").tabs({tabTemplate:'<li><a href="#{href}" class="ui-closable">#{label}</a><span class="icon ui-icon-close"></span></li',navigation:true,add:function(l,k){$(k.panel).html("<p>Please wait while your data is being grabbed...</p>");v.n=k.tab.innerHTML;$.ajax({type:"GET",url:"<?=base_url()?>tab/",data:v,success:function(p){$(k.panel).html(p)}})}});$(".ui-icon-close").live("click",function(){var l=$(this).parent().children("a").html();var k=$("li",n).index($(this).parent());n.tabs("remove",k);h.deleteElement(l)});$(".shrinkable").each(function(){$(this).click(function(){var k=$(this).parent().parent().children(".inner");if($(this).hasClass("expand")){$(this).addClass("shrink").removeClass("expand");k.slideDown(500)}else{$(this).addClass("expand").removeClass("shrink");k.slideUp(500)}})});var j=true;$("#contact").submit(function(){if(j){j=false;var k=$(this);k.find(".msg").attr("class","msg").show(500).text("Submitting your feedback...");$.post(k.attr("action"),k.serialize(),function(l){if(l=="SUCCESS"){k.find(".msg").text("Your feedback has been sent, thanks!").addClass("success").remove("error")}else{k.find(".msg").text("Please fill out the message field below.").addClass("error").remove("success")}j=true})}return false})});
</script>
<?php endif; ?>
<?php if(!IS_LOCAL_TEST)://Google analytics for non-local testing. ?>
<script type="text/javascript">var _gaq =_gaq||[];_gaq.push(['_setAccount','UA-24111550-1']);_gaq.push(['_trackPageview']);(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();</script>
<? endif; ?>
</head>
<body>
<div id="header">
<div id="header-wrap">
<p class="logo"><a href="<?=base_url()?>">RaidRifts</a></p>
<p class="catchphrase">Rift logs <span class="glow">made easy.</span></p>
<?php if($this->user->id): ?>
<?php if($this->user->guild > 0 && $this->user->has_permission(ACCESS_LOG_UPLOAD)): ?>
    <a href="<?=base_url()?>downloads/client/client0.2.1.jar" id="client_download">
        <span class="version">v0.2.1 English</span>
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