(function( $ ){

  var options = {
	begin		: -86400,
	end			: null,
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
	  this.data(myoptions);
	  //$(this).bind('dblclick', methods.reposition);
	  //$(this).bind('click', methods.timespan);
	  $(this).single_double_click(methods.timespan, methods.reposition);
	  $(this).bind('mousemove', methods.datetime);
      $(this).hover(function () {
	  	if (current_graph != '#'+$(this).attr('id')) {
			 $('#timespan').hide();
		}
	  	current_graph = '#'+$(this).attr('id');
      	var timebuttontop = $(this).offset().top + $(this).height() - $('#timebutton').height();
      	var timebuttonleft = $(this).offset().left + $(this).width() - $('#timebutton').width();
      	$('#timebutton').clearQueue().show().animate({ top: timebuttontop, left: timebuttonleft }, { queue: true, duration: 100 });
      	var datetimetop = $(this).offset().top + 20 ;
      	var datetimeleft = $(this).offset().left + ($(this).width() / 2) - ($('#datetime').width() / 2) + 17 ;
      	$('#datetime').clearQueue().show().animate({ top: datetimetop, left: datetimeleft }, { queue: true, duration: 100 });
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
	  			$('#modalwindow').jqxWindow({ title: 'Top process', isModal: false, theme: theme, width: 537, height: 600 }).show();
	  			$('#modalwindowcontent').html('<div id="table"></div>');
				var url = 'action.php?tpl=top&id='+json_item_datas['jstree']['id']+'&time='+toptime;
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
				var dataAdapter = new $.jqx.dataAdapter(source);
				console.log(dataAdapter);
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
			break;
			default:
				alert('Available soon ...');
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
	  this.pwgraph('check_boundary');
      $(this).attr('src',
		'graph.php'
		+ '?host=' + options['host']
		+ '&plugin=' + options['plugin']
		+ '&plugin_instance=' + options['plugin_instance']
		+ '&type=' + options['type']
		+ '&type_instance=' + options['type_instance']
		+ '&begin=' + options['begin']
		+ (options['end'] != null ? '&end=' + options['end'] : '')
		+ '&width=' + options['width']
		+ '&t=' + (new Date()).getTime()
	  );
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
	setts : function() {
	  var options = this.data();
	  $('.graph').each(function (i) {
	  	$(this).pwgraph('set_options', { begin: options['begin'], end: options['end'] }).pwgraph('display');
	  });
	  return this;
	},
	custd : function() {
	  $('#modalwindow').jqxWindow({ height: 287, width: 262, title: 'Select a date', isModal: true, theme: theme }).show();
	  $('#modalwindowcontent').html('<div id="calendar"></div>');
	  $('#calendar').jqxCalendar({ width: '250px', height: '250px', theme: theme });
	  $('#calendar').bind('cellSelected', function () {
		var newdate = $('#calendar').jqxCalendar('getSelectedDate');
		$('#modalwindow').jqxWindow('closeWindow');
		var begin = Math.round(newdate.getTime() / 1000);
		var end = begin + 86400;
		$(current_graph).pwgraph({ begin: begin, end: end }).pwgraph('display');
	  });
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
		if ($('#timespan').css('display') != 'none') {
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
