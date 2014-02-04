/**
 * PWGraph functions for Perfwatcher
 *
 * Copyright (c) 2011 Cyril Feraudet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 
(function( $ ){

  var options = {
	begin		: -86400,
	end			: null,
	zero		: 0,
	logarithmic	: 0,
	tinylegend	: 0,
	width		: 697,
	zoomXstart	: 0,
	gridXstart	: 67,
	gridXend	: 667,
	gridYstart	: 35,
	gridYend	: 155
  }

  var methods = {
    init : function( initoptions ) { 
      var pos_beforeopen_x = 0;
      var pos_beforeopen_y = 0;
	  var myoptions = options;
	  $.extend(myoptions, initoptions);
	  myoptions['althost'] = null;
	  this.data(myoptions);
	  //$(this).bind('dblclick', methods.reposition);
	  //$(this).bind('click', methods.timespan);
//	  $(this).single_double_click(methods.timespan, methods.reposition);
	  $(this).bind('mousemove', methods.mousemove);
	  $(this).bind('mousedown', methods.mousedown);
	  $(this).bind('mouseup', methods.mouseup);
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
      $("#itemtab").contextmenu({
        delegate: "img.graph",
        menu: "#graphmenu",
        beforeOpen: function(event, ui) {
            pos_beforeopen_x = event.clientX - $(ui.target).offset().left;
            pos_beforeopen_y = event.clientY - $(ui.target).offset().top;
            },
        select: function(event, ui) {
            switch(ui.cmd) {
                case 'top':
//                    var x = event.clientX - $(ui.target).position().left;
//                    var y = event.clientY - $(ui.target).position().top;
                    var x = pos_beforeopen_x;
                    var y = pos_beforeopen_y;
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
                case 'tinylegend':
                case 'zero':
                case 'logarithmic':
                    $(current_graph).pwgraph(ui.cmd).pwgraph('display');
                    break;
                default:
                    alert(ui.cmd + ' is not a known action ...');
                    break;
            }
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
                +"', '"+((options['plugin_instance'] == '_')?'':options['plugin_instance'])
                +"', '"+options['type']
                +"', '"+((options['type_instance'] == '_')?'':options['type_instance'])
                +"')";
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
      $(this).on('dragstart', function() { return false; });
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
      clipboard_append(options['clipboardtxt']);
	  return this;
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
    mousedown : function(event) {
        if(isRightClick(event)) return this;
        if(pwgraph_hover_enabled) {
            if (current_graph != '#'+$(this).attr('id')) {
                $('#timespan').hide();
            }
            var zone = $(this).attr('zone');
            if(pwgraph_current_zone == zone) {
        		var options = $(this).data();
        		var x = event.pageX - $(this).offset().left;
        		var y = event.pageY - $(this).offset().top;
        		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
        		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
        		x -= options['gridXstart'];
                options['zoomXstart'] = x;
                $(this).data(options);

                if ($('#timespan').css('display') != 'none') {
                    $('#timespan').hide();
                    return;
                }
                $('#timespan').show();
                $('#timespan').css({
                    top: $(this).offset().top + options['gridYstart'] - 3, 
                    left: $(this).offset().left + options['gridXstart'] + x,
                    width: 1,
                    height: options['gridYend'] - options['gridYstart'] + 1
                });

                $(this).bind('mouseout', methods.mouseout);
                $(this).unbind('mousedown');
                $('#timespan').bind('mousemove', function(event) { $(this).pwgraph('mousemove', event); } );
                $('#timespan').bind('mouseup', function(event) { $(this).pwgraph('mouseup', event); } );

        	}
    	}

	    return this;
    },
    mouseup : function(event) {
        $('#timespan').hide();
        $(current_graph).unbind('mouseout');
        $('#timespan').unbind('mousemove');
        $('#timespan').unbind('mouseup');
        $(current_graph).bind('mousedown', function(event) { $(current_graph).pwgraph('mousedown', event); });
        if(pwgraph_hover_enabled) {
            var zone = $(current_graph).attr('zone');
            if(pwgraph_current_zone == zone) {
        		var options = $(current_graph).data();
        		var x = event.pageX - $(current_graph).offset().left;
        		var y = event.pageY - $(current_graph).offset().top;
        		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
        		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
        		x -= options['gridXstart'];
                if((options['zoomXstart'] > 0) && (x != options['zoomXstart'])) {
                    var diff = options['end'] - options['begin'];
                    var step = diff / (options['gridXend'] - options['gridXstart']);
                    var rl = options['begin'] + Math.round(step * options['zoomXstart']);
                    var rw = Math.round(step * (x - options['zoomXstart']));
                    if(x - options['zoomXstart'] < 0) { 
                        rl = rl + rw;
                        rw = -rw;
                    }
                    options['begin'] = rl;
                    options['end'] = rl + rw;
                    $(current_graph).data(options);
                    $(current_graph).pwgraph('display');
                }
                options['zoomXstart'] = 0;
                $(current_graph).data(options);
        	}
    	}
	    return this;
    },
    mouseout : function(event) {
        if(event.relatedTarget.getAttribute('id') == "timespan") return this;
        if(pwgraph_hover_enabled) {
            if (current_graph != '#'+$(this).attr('id')) {
                $('#timespan').hide();
                $(this).unbind('mouseout');
                $('#timespan').unbind('mousemove');
            }
            var zone = $(this).attr('zone');
            if(pwgraph_current_zone == zone) {
        		var options = $(this).data();
                options['zoomXstart'] = 0;
                $(this).data(options);
                $('#timespan').hide();
                $(this).unbind('mouseout');
                $('#timespan').unbind('mousemove');
        	}
    	}
	    return this;
    },
//	TODO : remove this code
//	reposition : function(event) {
//	    var options = $(this).data();
//		var x = event.clientX - $(event.target).position().left;
//		var y = event.clientY - $(event.target).position().top;
//		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
//		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
//		x -= options['gridXstart'];
//		x -= (options['gridXend'] - options['gridXstart']) / 2;
//		var diff = options['end'] - options['begin'];
//		var step = diff / (options['gridXend'] - options['gridXstart']);
//		options['begin'] += Math.round(step * x);
//		options['end'] += Math.round(step * x);
//	    $(this).data(options);
//		$(this).pwgraph('display');
//	    return this;
//	},
//	applytimespan : function() {
//	    var options = $(this).data();
//		x = $('#timespan').position().left - $(this).position().left - options['gridXstart'];
//		var diff = options['end'] - options['begin'];
//		var step = diff / (options['gridXend'] - options['gridXstart']);
//		options['begin'] += Math.round(step * x);
//		options['end'] = options['begin'] + Math.round(step * $('#timespan').width());
//	    $(this).data(options);
//	  	return this;
//	},
//	timespan : function(event) {
//	    var options = $(this).data();
//		var x = event.clientX - $(this).offset().left;
//		var y = event.clientY - $(this).offset().top;
//		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
//		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
//		x -= 40;
//		if ($('#timespan').css('display') != 'none') {
//			$('#timespan').hide();
//			return;
//		}
//		$('#timespan').show();
//		$('#timespan').animate({ 
//			top: $(event.target).position().top + options['gridYstart'] - 3, 
//			left: $(event.target).position().left + x,
//			width: 80,
//			height: options['gridYend'] - options['gridYstart'] + 1
//		}, { 
//			queue: false, duration: 200,
//			complete : function() {
//				if ($('#timespan').position().left < $(event.target).position().left + options['gridXstart']) {
//					$('#timespan').animate({ left: $(event.target).position().left + options['gridXstart'] } , { queue: true, duration: 300 });
//				}
//				if ($('#timespan').position().left + $('#timespan').width() > $(event.target).position().left + options['gridXend']) {
//					$('#timespan').animate({ left: $(event.target).position().left + options['gridXend'] - $('#timespan').width() } , { queue: true, duration: 300 });
//				}
//			}
//		});
//		$('#timespan').resizable({
//            autoHide:   true,
//            minHeight:  options['gridYend'] - options['gridYstart'],
//            maxHeight:  options['gridYend'] - options['gridYstart'],
//            maxWidth:   (options['gridXend'] - x ),
//            minWidth:   20,
//            stop: function(e2, ui) {
//                $('#timespan').clearQueue().draggable('option', 'containment', [
//                    $(event.currentTarget).offset().left + options['gridXstart'],
//                    $(event.currentTarget).offset().top + options['gridYstart'],
//                    $(event.currentTarget).offset().left + options['gridXend'] - parseInt($('#timespan').css('width')),
//                    $(event.currentTarget).offset().top + options['gridYstart']
//                ]
//                );
//            }
//		}).draggable({
//            axis: "x",
//            appendTo: event.currentTarget,
//            containment: [
//                $(event.currentTarget).offset().left + options['gridXstart'],
//                $(event.currentTarget).offset().top + options['gridYstart'],
//                $(event.currentTarget).offset().left + options['gridXend'] - parseInt($('#timespan').css('width')),
//                $(event.currentTarget).offset().top + options['gridYstart']
//            ],
//            stop: function() {
//                $('#timespan').clearQueue().resizable('option', 'maxWidth', 
//                $(event.currentTarget).offset().left 
//                + event.currentTarget.clientWidth 
//                - parseInt($('#timespan').css('left')) 
//                -29
//                );
//            }
//		});
//	},
    mousemove : function(event) {
        if(event.target.getAttribute('id') == 'timespan') {
        		var options = $(current_graph).data();
        		var x = event.pageX - $(current_graph).offset().left;
        		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
        		x -= options['gridXstart'];
                $(current_graph).data(options);

                // Set datetime
        		var diff = options['end'] - options['begin'];
        		var step = diff / (options['gridXend'] - options['gridXstart']);
        		var curdate = new Date((options['begin'] + Math.round(step * x)) * 1000);
        		$('#datetime').html(curdate.toString());

                // selection
                var dl = options['zoomXstart'] + $(current_graph).offset().left + options['gridXstart'];
                var dw = x - options['zoomXstart'];
                if(x - options['zoomXstart'] < 0) { 
                    dl = dl + dw;
                    dw = -dw;
                }
                $('#timespan').css({
                    left: dl,
                    width: dw,
                });
        } else {
            $(this).pwgraph('datetime', event);
        }
    },
	datetime : function(event) {
        if(pwgraph_hover_enabled) {
            if (current_graph != '#'+$(this).attr('id')) {
                $('#timespan').hide();
                $(this).unbind('mouseout');
                $('#timespan').unbind('mousemove');
            }
            var zone = $(this).attr('zone');
            if(pwgraph_current_zone == zone) {
        		var options = $(this).data();
        		var x = event.pageX - $(this).offset().left;
        		var y = event.pageY - $(this).offset().top;
        		if (x < options['gridXstart'] || x > options['gridXend']) { return this; }
        		if (y < options['gridYstart'] || y > options['gridYend']) { return this; }
        		x -= options['gridXstart'];

                // Set datetime
        		var diff = options['end'] - options['begin'];
        		var step = diff / (options['gridXend'] - options['gridXstart']);
        		var curdate = new Date((options['begin'] + Math.round(step * x)) * 1000);
        		$('#datetime').html(curdate.toString());

                // selection
                if(options['zoomXstart'] > 0) {
                var dl = options['zoomXstart'] + $(this).offset().left + options['gridXstart'];
                var dw = x - options['zoomXstart'];
                if(x - options['zoomXstart'] < 0) { 
                    dl = dl + dw;
                    dw = -dw;
                }
                $('#timespan').css({
                    left: dl,
                    width: dw,
                });
                }
        	}
    	}
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
    m += 1;
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
            maxHeight: $(window).height() - 50,
            position: {my: 'right top', at: 'right-12 top+12', of: '#items' },
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                $.timeline = {};
                pwgraph_hover_enabled = true;
            },
            open: function(event, ui) {
                $('#timebutton').hide();
                $('#timespan').hide();
                $('#datetime').hide();
                pwgraph_hover_enabled = false;

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
                +'<div id="topprocessgrid">'
                +'</div>'
                )
        .dialog({
            autoOpen: true,
            appendTo: '#modaldialog',
            title: 'Top process for '+host+' at '+tm_to_ddmmyy_hhmmss(toptime),
            width: 645,
            height: 620,
            maxHeight: $(window).height() - 50,
            position: {my: 'right top', at: 'right-12 top+12', of: '#items' },
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                $('#topprocesstable').remove();
                pwgraph_hover_enabled = true;
            },
            open: function(event, ui) {
                $('#timebutton').hide();
                $('#timespan').hide();
                $('#datetime').hide();
                pwgraph_hover_enabled = false;

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
    $('#topprocessgrid').html(
            '  <table id="topprocesstable"></table>'
            +'  <div id="topprocessdiv"></div>'
            );
	var url = 'action.php?tpl=top&view_id='+view_id+'&cdsrc='+cdsrc+'&host='+host+'&time='+toptime;
    $('#topprocesstable').jqGrid({
        url: url,
        datatype: "json",
        colNames: ['PID','User','Group','Memory','CPU','Process'],
        colModel: [
            {name: 'pid', index: 'pid', sortable: false},
            {name: 'userlabel', index: 'userlabel'},
            {name: 'grouplabel', index: 'grouplabel'},
            {name: 'rss', index: 'rss', sorttype: 'int', align: 'right', formatter: function(cellvalue, options, rowObject) { return bytesToSize(cellvalue); }},
            {name: 'cpu', index: 'cpu', sorttype: 'int', align: 'right', formatter: function(cellvalue, options, rowObject) { return cellvalue+' %'; }},
            {name: 'process', index: 'process', sortable: false}
            ],
        rowNum: 20,
        rowList: [10,20,30],
        pager: '#topprocessdiv',
        sortname: 'cpu',
        sortorder: 'asc',
        height: 'auto',
        width: 630,
        loadonce: true,
        loadComplete: function(data) {
            $('#modaldialogcontents').dialog("option", "title", 'Top process for '+host+' at '+tm_to_ddmmyy_hhmmss(data.userdata.date2));
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
