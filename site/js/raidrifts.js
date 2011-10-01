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
		url: path+'members/ajax/guilds/',
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
		$.post(path+'members/ajax/deletelog/', data, function(data) {
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
					window.location = path+'register/complete/';
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
			$.post(path+'members/ajax/application/', {
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
			$.post(path+'members/ajax/kick/', {
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
				url:path+'report/tab/',
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