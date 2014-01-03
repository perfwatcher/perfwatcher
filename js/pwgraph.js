(function( $ ){

  var options = {
	begin		: -86400,
	end			: null,
	zero		: 0,
	logarithmic	: 0,
	tinylegend	: 0,
	width		: 697,
	gridXstart	: 67,
	gridXend	: 667,
	gridYstart	: 35,
	gridYend	: 155
  }

  var methods = {
    init : function( initoptions ) { 
	  var myoptions = options;
	  $.extend(myoptions, initoptions);
	  myoptions['althost'] = null;
	  this.data(myoptions);
	  //$(this).bind('dblclick', methods.reposition);
	  //$(this).bind('click', methods.timespan);
	  $(this).single_double_click(methods.timespan, methods.reposition);
	  $(this).bind('mousemove', methods.datetime);
      $(this).hover(function () {
        if(pwgraph_hover_enabled) {
            if (current_graph != '#'+$(this).attr('id')) {
                $('#timespan').hide();
            }
            var zone = $(this).attr('zone');
            if(pwgraph_current_zone == zone) {
              current_graph = '#'+$(this).attr('id');
              var timebuttontop = $(this).offset().top + $(this).height() - $('#timebutton').height();
              var timebuttonleft = $(this).offset().left + $(this).width() - $('#timebutton').width();
              $('#timebutton').clearQueue().show().animate({ top: timebuttontop, left: timebuttonleft }, { queue: true, duration: 100 });
              var datetimetop = $(this).offset().top + 20 ;
              var datetimeleft = $(this).offset().left + ($(this).width() / 2) - ($('#datetime').width() / 2) + 17 ;
              $('#datetime').clearQueue().show().animate({ top: datetimetop, left: datetimeleft }, { queue: true, duration: 100 });
          }
        }
      });
	  $(this).contextMenu({ menu: 'graphmenu' }, function(action, el, pos) {
		switch(action) {
        	case 'top':
			var x = pos.docX - $(current_graph).position().left;
			var y = pos.docY - $(current_graph).position().top;
			var options = $(current_graph).data();
			if (y < options['gridYstart'] || y > options['gridYend']) { break; }
			if (x < options['gridXstart'] || x > options['gridXend']) { break; }
			x -= options['gridXstart'];
			var diff = options['end'] - options['begin'];
			var step = diff / (options['gridXend'] - options['gridXstart']);
			var toptime = options['begin'] + Math.round(step * x);
			showtop(options['cdsrc'], $(current_graph).data().host, toptime);
		break;
       	case 'timeline':
			var options = $(current_graph).data();
			showtimeline(options['cdsrc'], $(current_graph).data().host,options['begin'],options['end']);
		break;
       	case 'save':
			var url = $(current_graph).attr('src') + '&download';
			document.location = url;
		break;
       	case 'export':
			var url = $(current_graph).attr('src');
			document.location = url.replace('graph.php', 'export.php');
		break;
		case 'tinylegend':
		case 'zero':
		case 'logarithmic':
			$(current_graph).pwgraph(action).pwgraph('display');
		break;
		default:
			alert(action + ' Available soon ...');
		break;
		}
	  });
	  return this;
    },
	set_options : function( initoptions) {
		var myoptions = this.data();
		$.extend(myoptions, initoptions);
		this.data(myoptions);
		return this;
	},
    display : function( ) {
	  var options = this.data();
      options['rrdid'] = options['cdsrc']
            +'/'+options['host']
            +'/'+options['plugin']
            +((options['plugin_instance'] == '_')?'':('-'+options['plugin_instance']))
            +'/'+options['type']
            +((options['type_instance'] == '_')?'':('-'+options['type_instance']));
      options['clipboardtxt'] = "rrdgraph('"+options['cdsrc']
                +"', '"+options['host']
                +"', '"+options['plugin']
                +"', '"+options['plugin_instance']
                +"', '"+options['type']
                +"', '"+options['type_instance']+"')";
      this.data(options);
      this.pwgraph('check_boundary');
      $(this).attr('src',
		'graph.php'
		+ '?collectd_source=' + encodeURIComponent(options['cdsrc'])
		+ '&host=' + encodeURIComponent(options['host'])
		+ (options['althost'] != null ? '&althost=' + encodeURIComponent(options['althost']) : '')
		+ '&plugin=' + encodeURIComponent(options['plugin'])
		+ '&plugin_instance=' + encodeURIComponent(options['plugin_instance'])
		+ '&type=' + encodeURIComponent(options['type'])
		+ '&type_instance=' + encodeURIComponent(options['type_instance'])
		+ '&begin=' + options['begin']
		+ (options['end'] != null ? '&end=' + options['end'] : '')
		+ '&width=' + options['width']
		+ '&zero=' + options['zero']
		+ '&logarithmic=' + options['logarithmic']
		+ '&tinylegend=' + options['tinylegend']
		+ '&t=' + (new Date()).getTime()
	  );
      $(this).addClass('ui-widget-content');
      return this;
    },
    check_boundary : function( ) { 
	  var options = this.data();
	  var now = Math.round((new Date()).getTime() / 1000);
      if (options['begin'] < 0) { 
	  	options['begin'] += now;
	  }
	  if (options['end'] == null) {
		options['end'] = now;
	  }
	  this.data(options);
    },
	time_change : function( factors ) { 
	  var options = this.data();
	  var diff = options['end'] - options['begin'];
	  if ((diff <= 300) && (factors['factor_begin'] > 0.0) && (factors['factor_end'] < 0.0)) {
		return this;
	  }
	  options['begin'] 	+= Math.round(diff * factors['factor_begin']);
	  options['end'] 	+= Math.round(diff * factors['factor_end']);
	  this.data(options);
	  return this;
	},
    later : function() { 
      return this.pwgraph('time_change', { factor_begin: +0.2, factor_end: +0.2} );
    },
    earlier : function() { 
      return this.pwgraph('time_change', { factor_begin: -0.2, factor_end: -0.2} );
    },
    zoomin : function() { 
      return this.pwgraph('time_change', { factor_begin: +0.2, factor_end: -0.2} );
    },
    zoomout : function() { 
      return this.pwgraph('time_change', { factor_begin: (-1.0 / 3.0), factor_end: (1.0 / 3.0)} );
    },
    clipadd : function() {
	  var options = this.data();
      add_to_clipboard(options['clipboardtxt']);
    },
    curh : function() { 
	  var options = this.data();
	  options['begin'] = -3600;
	  options['end'] = null;
	  this.data(options);
      return this;
    },
    curd : function() { 
	  var options = this.data();
	  options['begin'] = -86400;
	  options['end'] = null;
	  this.data(options);
      return this;
    },
    curw : function() { 
	  var options = this.data();
	  options['begin'] = -86400 * 7;
	  options['end'] = null;
	  this.data(options);
      return this;
    },
    curm : function() { 
	  var options = this.data();
	  options['begin'] = -86400 * 31;
	  options['end'] = null;
	  this.data(options);
      return this;
    },
    cury : function() { 
	  var options = this.data();
	  options['begin'] = -86400 * 366;
	  options['end'] = null;
	  this.data(options);
      return this;
    },
    zero : function() { 
	  var options = this.data();
	  if (options['zero'] == 1)
	  	options['zero'] = 0;
	  else
	  	options['zero'] = 1;
	  this.data(options);
      return this;
    },
    logarithmic : function() { 
	  var options = this.data();
	  if (options['logarithmic'] == 1)
	  	options['logarithmic'] = 0;
	  else {
	  	options['logarithmic'] = 1;
	  	options['zero'] = 0;
	  }
	  this.data(options);
      return this;
    },
    tinylegend : function() { 
	  var options = this.data();
	  if (options['tinylegend'] == 1)
	  	options['tinylegend'] = 0;
	  else
	  	options['tinylegend'] = 1;
	  this.data(options);
      return this;
    },
	setts : function() {
	  var options = this.data();
      var zone = $(this).attr('zone');
	  $('.graph[zone="'+zone+'"]').each(function (i) {
	  	$(this).pwgraph('set_options', { begin: options['begin'], end: options['end'] }).pwgraph('display');
	  });
	  return this;
	},
	custd : function() {
      var options = this.data();
      pwgraph_hover_enabled = false;
      $('<div id="modaldialogcontents"></div>')
          .html('<div>'
                    +'<h1>Current graph</h1>'
                    +'<p>'+options['rrdid']+'</p>'
                    +'<h1>Select the start date</h1>'
                    +'</div>'
                    +'<div id="calendar"></div>'
               )
          .dialog({
              autoOpen: true,
              appendTo: '#modaldialog',
              position: {my: 'center', at: 'center', of: '#items' },
              title: 'Select a date',
              close: function(event,ui) {
                  $('#modaldialog').hide();
                  $('#modaldialogcontents').html("");
                  pwgraph_hover_enabled = true;
                  $(this).dialog('destroy').remove();
              },
              open: function(event, ui) {
                  $('#modaldialog').show();
                  $('#calendarresultdiv').hide();
                  $('#calendar').datepicker({
                      changeMonth: true,
                      changeYear: true,
                      dateFormat: "@",
                      onSelect: function(d, inst) {
                              var options = $(current_graph).data();
                              options['begin'] = Math.round(d / 1000);
                              options['end'] = options['begin'] + 86400;
                              $('#modaldialogcontents').dialog('close');
                              $(current_graph).data(options);
                              $(current_graph).pwgraph('display');
                          }
                    });
              }
          })
          .show();
	  return this;
	},
	reposition : function(event) {
	    var options = $(this).data();
		var x = event.clientX - $(event.target).position().left;
		var y = event.clientY - $(event.target).position().top;
		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
		x -= options['gridXstart'];
		x -= (options['gridXend'] - options['gridXstart']) / 2;
		var diff = options['end'] - options['begin'];
		var step = diff / (options['gridXend'] - options['gridXstart']);
		options['begin'] += Math.round(step * x);
		options['end'] += Math.round(step * x);
	    $(this).data(options);
		$(this).pwgraph('display');
	    return this;
	},
	applytimespan : function() {
	    var options = $(this).data();
		x = $('#timespan').position().left - $(this).position().left - options['gridXstart'];
		var diff = options['end'] - options['begin'];
		var step = diff / (options['gridXend'] - options['gridXstart']);
		options['begin'] += Math.round(step * x);
		options['end'] = options['begin'] + Math.round(step * $('#timespan').width());
	    $(this).data(options);
	  	return this;
	},
	timespan : function(event) {
	    var options = $(this).data();
		var x = event.clientX - $(event.target).position().left;
		var y = event.clientY - $(event.target).position().top;
		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
		x -= 40;
		if ($('#timespan').css('display') != 'tab') {
			$('#timespan').hide();
			return;
		}
		$('#timespan').show();
		$('#timespan').animate({ 
			top: $(event.target).position().top + options['gridYstart'] - 3, 
			left: $(event.target).position().left + x,
			width: 80,
			height: options['gridYend'] - options['gridYstart'] + 1
		}, { 
			queue: false, duration: 200,
			complete : function() {
				if ($('#timespan').position().left < $(event.target).position().left + options['gridXstart']) {
					$('#timespan').animate({ left: $(event.target).position().left + options['gridXstart'] } , { queue: true, duration: 300 });
				}
				if ($('#timespan').position().left + $('#timespan').width() > $(event.target).position().left + options['gridXend']) {
					$('#timespan').animate({ left: $(event.target).position().left + options['gridXend'] - $('#timespan').width() } , { queue: true, duration: 300 });
				}
			}
		});
		$('#timespan').resizable({
            autoHide:   true,
            minHeight:  options['gridYend'] - options['gridYstart'],
            maxHeight:  options['gridYend'] - options['gridYstart'],
            maxWidth:   (options['gridXend'] - x ),
            minWidth:   20,
            stop: function(e2, ui) {
                $('#timespan').clearQueue().draggable('option', 'containment', [
                    $(event.currentTarget).offset().left + options['gridXstart'],
                    $(event.currentTarget).offset().top + options['gridYstart'],
                    $(event.currentTarget).offset().left + options['gridXend'] - parseInt($('#timespan').css('width')),
                    $(event.currentTarget).offset().top + options['gridYstart']
                ]
                );
            }
		}).draggable({
            axis: "x",
            appendTo: event.currentTarget,
            containment: [
                $(event.currentTarget).offset().left + options['gridXstart'],
                $(event.currentTarget).offset().top + options['gridYstart'],
                $(event.currentTarget).offset().left + options['gridXend'] - parseInt($('#timespan').css('width')),
                $(event.currentTarget).offset().top + options['gridYstart']
            ],
            stop: function() {
                $('#timespan').clearQueue().resizable('option', 'maxWidth', 
                $(event.currentTarget).offset().left 
                + event.currentTarget.clientWidth 
                - parseInt($('#timespan').css('left')) 
                -29
                );
            }
		});
	},
	datetime : function(event) {
		var options = $(this).data();
		var x = event.pageX - $(event.target).position().left;
		var y = event.pageY - $(event.target).position().top;
		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
		x -= options['gridXstart'];
		//x -= (options['gridXend'] - options['gridXstart']);
		var diff = options['end'] - options['begin'];
		var step = diff / (options['gridXend'] - options['gridXstart']);
		var curdate = new Date((options['begin'] + Math.round(step * x)) * 1000);
		$('#datetime').html(curdate.toString());
	}
  };

  $.fn.pwgraph = function( method ) {
    // Method calling logic
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.pwgraph' );
    }    
  
  };
})( jQuery );

function tm_to_ddmmyy_hhmmss (tm) {
    var my_date = new Date(tm * 1000);
    var y = my_date.getFullYear();
    var m = my_date.getMonth();
    var d = my_date.getDate();
    m = (m<10) ? '0'+m : m;
    d = (d<10) ? '0'+d : d;

    var h = my_date.getHours();
    var min = my_date.getMinutes();
    var s = my_date.getSeconds();
    h = (h<10) ? '0'+h : h;
    min = (min<10) ? '0'+min : min;
    s = (s<10) ? '0'+s : s;

    return(d + '/' + m + '/' + y + ' ' + h + ':' + min + ':' + s);
}

// Timeline global var
$.timeline = {};
// $.timeline.jsondata : raw data from jsonrpc server
// $.timeline.displaydata : formatted data for timeline.draw()
// $.timeline.cdsrc : Collectd source for host
// $.timeline.host : host name
// $.timeline.tm_start : tm of the start of the timeline
// $.timeline.tm_end : tm of the end of the timeline
// $.timeline.show_pid_uid_gid : show or hide PID, UID and GID in the timeline.
// $.timeline.ignore_resident : show or hide processes that are running and do not stop between tm_start and tm_end

function parse_timeline_data() {

    $.timeline.displaydata = [];

    if($.timeline.jsondata['result']['status'] == 'OK') {
        for(var i in $.timeline.jsondata['result']['timeline']) {
            $.timeline.displaydata.push({
                    'start': new Date(1000*$.timeline.jsondata['result']['timeline'][i]['start']),
                    'end': new Date(1000*$.timeline.jsondata['result']['timeline'][i]['end']),
                    //                                    'group': $.timeline.jsondata['result']['timeline'][i]['ppid'],
                    'content': $.timeline.jsondata['result']['timeline'][i]['cmd']
                    +($.timeline.show_pid_uid_gid?'<br />PID: '+$.timeline.jsondata['result']['timeline'][i]['pid']
                        +'<br />UID: '+$.timeline.jsondata['result']['timeline'][i]['uname']
                        +'<br />GID: '+$.timeline.jsondata['result']['timeline'][i]['gname'] : "")
                    });
        }

    } else if($.timeline.jsondata['result']['status'] == 'TIMEOUT') {
        notify_ko('jsonrpc error : TIMEOUT');
    } else {
        notify_ko('jsonrpc error : '+$.timeline.jsondata['result']['status']);
    };
}

function display_timeline() {
    $('#timeline').html("<p>Data received.</p><p>Building Timeline...</p>");
    var timeline = new links.Timeline(document.getElementById('timeline'));
    var timeline_options = {};
    timeline_options = {
        "width":  "100%",
        "height": "auto",
        "min": new Date(1000*$.timeline.tm_start),
        "max": new Date(1000*$.timeline.tm_end),
        "style": "box",
        "animate": false,
        "animateZoom": false,
        "minHeight": 200,
        "selectable": false,
        "showNavigation": true
    };

    // Draw our timeline with the created $.timeline.displaydata and options
    timeline.draw($.timeline.displaydata, timeline_options);
}

function load_timeline_data() {
    $.ajax({
        async : true,
        type: 'POST',
        url: "action.php?tpl=jsonrpc&cdsrc="+$.timeline.cdsrc,
        data: JSON.stringify({"jsonrpc": "2.0", "method": "topps_get_timeline", "params": {
            "hostname" : $.timeline.host,
            "start_tm" : $.timeline.tm_start,
            "end_tm" : $.timeline.tm_end,
            "interval" : 0,            /* hard coded option; maybe the user could set it with a form ? */
            "ignore_short_lived" : 30, /* hard coded option; maybe the user could set it with a form ? */
            "ignore_resident" : $.timeline.ignore_resident,
            "timeout" : 60
            }, "id": 0}),
        dataType : 'json',
        complete : function (r) {
            if(r.status) {
                $.timeline.jsondata = jQuery.parseJSON(r.responseText);

                parse_timeline_data();
                display_timeline();

            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var error =  jQuery.parseJSON(XMLHttpRequest['responseText']);
            notify_ko('jsonrpc error : '+error['error']['message']+' (code : '+error['error']['code']+')');
        }
    });

}

function timeline_update_buttons() {
    $('#switch_show_pid_uid_gid').html(($.timeline.show_pid_uid_gid?"Hide":"Show") + " PID, UID and GID");
    $('#switch_ignore_resident').html(($.timeline.ignore_resident?"Show":"Hide") + " resident processes");
}

function showtimeline (cdsrc, host, tm_start, tm_end) {
    var timeline_width = 1000;
    var timeline_height = 600;
    $('<div id="modaldialogcontents"></div>')
        .html(
                '<form id="timelineoptions"></form>'
                +'<div id="timelinebuttons">'
                +'  <button id="switch_show_pid_uid_gid"></button>'
                +'  <button id="switch_ignore_resident"></button>'
                +'</div>'
                +'<div id="timeline"><p>Waiting for data...</p></div>'
                )
        .dialog({
            autoOpen: true,
            appendTo: '#modaldialog',
            title: 'Timeline for '+host+' between '+tm_to_ddmmyy_hhmmss(tm_start)+' and '+tm_to_ddmmyy_hhmmss(tm_end),
            width: timeline_width,
            height: timeline_height,
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                $.timeline = {};
            },
            open: function(event, ui) {
                // Set timeline options
                $.timeline = {};
                $.timeline.show_pid_uid_gid = true;
                $.timeline.tm_start = tm_start;
                $.timeline.tm_end = tm_end;
                $.timeline.cdsrc = cdsrc;
                $.timeline.host = host;
                $.timeline.ignore_resident = true;

                $('#modaldialog').show();
                load_timeline_data();
                timeline_update_buttons();

                $('#switch_show_pid_uid_gid').click(function() {
                    $.timeline.show_pid_uid_gid = ! $.timeline.show_pid_uid_gid;
                    parse_timeline_data();
                    display_timeline();
                    timeline_update_buttons();
                });
                $('#switch_ignore_resident').click(function() {
                    $.timeline.ignore_resident = ! $.timeline.ignore_resident;
                    $('#timeline').html("<p>Waiting for data...</p>");
                    load_timeline_data();
                    parse_timeline_data();
                    display_timeline();
                    timeline_update_buttons();
                });
          }
    });
}

$.top = {};

function showtop (cdsrc, host, toptime) {
    $.top.time = toptime;
    $('<div id="modaldialogcontents"></div>')
        .html(
                '<table id="topprocess" width="100%"><tr>'
                +  '<td class="prev" width="50%"><b>&#x2190;</b> previous</td>'
                +  '<td width="50%" class="next" style="text-align: right;">next <b>&#x2192;</b></td>'
                +'</tr></table>'
                +'<div id="table"></div>'
                )
        .dialog({
            autoOpen: true,
            appendTo: '#modaldialog',
            title: 'Top process for '+host+' at '+tm_to_ddmmyy_hhmmss(toptime),
            width: 545,
            height: 620,
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                $('#table').remove();
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                load_top(cdsrc, host, $.top.time);
          }
    });
	$('#topprocess .prev').click(function() {
        $.top.time = $.top.time - 60;
		load_top(cdsrc, host, $.top.time);
	});
	$('#topprocess .next').click(function() {
        $.top.time = $.top.time + 60;
		load_top(cdsrc, host, $.top.time);
	});
}

function load_top(cdsrc, host, toptime) {
	var url = 'action.php?tpl=top&view_id='+view_id+'&cdsrc='+cdsrc+'&host='+host+'&time='+toptime;
	var source = { 
		datatype: "json", 
		datafields: [ 
			{ name: 'userlabel' }, 
			{ name: 'grouplabel' }, 
			{ name: 'rss', type: 'int' },
			{ name: 'process' } ,
			{ name: 'pid', type: 'int' }, 
			{ name: 'cpu', type: 'int' }
		], 
		id: 'id', 
		url: url, 
		root: "data",
	};
	var dataAdapter = new $.jqx.dataAdapter(source, {
                downloadComplete: function (data, status, xhr) {
			if (data.date2) {
				var topdate = new Date(data.date2 * 1000).toString();
                $('#modaldialogcontents').dialog("option", "title", 'Top process for '+host+' at '+tm_to_ddmmyy_hhmmss(data.date2));
			} else if (data.error) {
				if (data.error.result && data.error.result.status == 'path not found or no file for this tm') {
                    $('#modaldialogcontents').dialog("option", "title", 'ERROR : No data for this date '+(new Date(toptime * 1000).toString()));
				} else {
                    $('#modaldialogcontents').dialog("option", "title", 'ERROR : No data for this date '+data.error);
				} 
			}
		},
                loadComplete: function (data) { },
                loadError: function (xhr, status, error) { }
        });
	var cpurenderer = function (row, column, value) {
		return '<span style="margin: 4px; float: right;">'+value+'%</span>';
	}
	var rssrenderer = function (row, column, value) {
		return '<span style="margin: 4px; float: right;">'+bytesToSize(value)+'</span>';
	}
	$('#table').jqxGrid({
		width: 525,
		height: 564,
	    source: dataAdapter,
	    theme: theme,
		sortable: true,
		altrows: true,
	    columns: [
	      { text: 'PID', datafield: 'pid', cellsalign: 'right', width: 45 },
	      { text: 'User', datafield: 'userlabel', width: 70 },
	      { text: 'Group', datafield: 'grouplabel', width: 70 },
	      { text: 'Memory', datafield: 'rss', cellsalign: 'right', width: 70, cellsrenderer: rssrenderer },
	      { text: 'CPU', datafield: 'cpu', cellsalign: 'right', width: 50, cellsrenderer: cpurenderer },
	      { text: 'Process', datafield: 'process', width: 200 }
	  	],
		ready: function () {
	    	$("#table").jqxGrid('sortby', 'cpu', 'desc');
		}
	});
}

// From http://stackoverflow.com/questions/1773069/using-jquery-to-compare-two-arrays
(function( $ ){
	$.fn.compare = function(t) {
		if (this.length != t.length) { return false; }
		var a = this.sort(),
			b = t.sort();
		for (var i = 0; i < a.length; i++) {
			if (a[i] !== b[i]) { 
				return false;
			}
		}
		return true;
	};
})( jQuery );
// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
