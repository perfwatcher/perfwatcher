(function( $ ){

  var options = {
	begin		: -86400,
	end			: null,
	width		: 697,
	gridXstart	: 68,
	gridXend	: 668,
	gridYstart	: 35,
	gridYend	: 155
  }

  var methods = {
    init : function( initoptions ) { 
	  var myoptions = options;
	  $.extend(myoptions, initoptions);
	  this.data(myoptions);
	  $(this).bind('dblclick', methods.reposition);
	  $(this).bind('click', methods.datetime);
	  $(this).bind('mousemove', methods.datetime);
      $(this).hover(function () {
	  	current_graph = '#'+$(this).attr('id');
      	var timebuttontop = $(this).offset().top + $(this).height() - $('#timebutton').height();
      	var timebuttonleft = $(this).offset().left + $(this).width() - $('#timebutton').width();
      	$('#timebutton').clearQueue().show().animate({ top: timebuttontop, left: timebuttonleft }, { queue: true, duration: 100 });
      	var datetimetop = $(this).offset().top + 20 ;
      	var datetimeleft = $(this).offset().left + ($(this).width() / 2) - ($('#datetime').width() / 2) + 17 ;
      	$('#datetime').clearQueue().show().animate({ top: datetimetop, left: datetimeleft }, { queue: true, duration: 100 });
      });
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
	  $('.graph').each(function (i, elem) {
	  	$(elem).pwgraph({ begin: options['begin'], end: options['end']}).pwgraph('display');
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
		var x = event.clientX - event.currentTarget.offsetLeft;
		var y = event.clientY - event.currentTarget.offsetTop;
		if (x < options['gridXstart'] || x > options['gridXend']) {
			return this;
		}
		if (y < options['gridYstart'] || y > options['gridYend']) {
			return this;
		}
		x -= options['gridXstart'];
		x -= (options['gridXend'] - options['gridXstart']) / 2;
		var diff = options['end'] - options['begin'];
		var step = diff / (options['gridXend'] - options['gridXstart']);
		options['begin'] += Math.round(step * x);
		options['end'] += Math.round(step * x);
	    $(this).data(options);
		$(this).pwgraph('display');
	},
	datetime : function(event) {
		//console.log(event.pageX, event.currentTarget.offsetLeft, event.originalEvent.view.tempX1, event);
		console.log($(event.target).position());
		var options = $(this).data();
		var x = event.pageX - $(event.target).position().left;
		if (x < options['gridXstart'] || x > options['gridXend']) {
			return this;
		}
		x -= options['gridXstart'];
		//x -= (options['gridXend'] - options['gridXstart']);
		var diff = options['end'] - options['begin'];
		var step = diff / (options['gridXend'] - options['gridXstart']);
		var date = new Date((options['begin'] + Math.round(step * x)) * 1000);
		$('#datetime').html(date.toLocaleString());
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
