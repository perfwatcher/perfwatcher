//
// Perfwatcher helpers for markdown
// rrdgraph(source,host,plugin,plugin_instance,type,type_instance) -> a graph !
//
// Showdown extension follows.
//

function pwmarkdown_trim_and_remove_quotes(str) {
	str = str.replace(/^[ \t]*|[ \t]*$/g, '');
	if(
		((str.charAt(0) === "\"") && (str.charAt(str.length-1) === "\""))
			||
		((str.charAt(0) === "'") && (str.charAt(str.length-1) === "'"))
	  ) { 
	str = str.substring(1, str.length-1); 
	}
	return(str);
}

function pwmarkdown_rrdgraph_escape(str, dash) {
	str = str.replace(/[\/"']/g, '_');
	if(dash) str = str.replace(/-/g, '_');
	return(str);
}

function pwmarkdown_filter(text) {
	text = text.replace(/rrdgraph[ \t]*\(([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*)\)/gm, 
			function(wholeMatch, cdsrc, host, p, pi, t, ti) {
			cdsrc = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(cdsrc), false);
			host = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(host), false);
			p = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(p), true);
			pi = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(pi), false);
			t = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(t), true);
			ti = pwmarkdown_rrdgraph_escape(pwmarkdown_trim_and_remove_quotes(ti), false);
			if(cdsrc == '') return(wholeMatch);
			if(host == '') return(wholeMatch);
			if(p == '') return(wholeMatch);
			if(t == '') return(wholeMatch);
			if(pi == '_') pi = '';
			if(ti == '_') ti = '';

			src = JSON.stringify({ 
					"cdsrc": cdsrc,
					"host": host,
					"plugin": p,
					"plugininstance": pi,
					"type": t,
					"typeinstance": ti
			});
			return('<span class="rrdgraph_to_render">'+encodeURIComponent(src)+'</span>');
	});
	return text ;
}

//
// Showdown extension
//

(function(){
  var rrdgraph = function(converter) {
	return ([
		{ type: 'lang', filter: function(text) { return pwmarkdown_filter(text); } }
	]);
  };

  // Client-side export
  if (typeof window !== 'undefined' && window.Showdown && window.Showdown.extensions) { window.Showdown.extensions.rrdgraph = rrdgraph; }
  // Server-side export
  if (typeof module !== 'undefined') {
    module.exports = rrdgraph;
  }
}());

