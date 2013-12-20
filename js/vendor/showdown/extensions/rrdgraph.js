//
// Perfwatcher extension for rrd graphs
// rrdgraph(source,host,plugin,plugin_instance,type,type_instance) -> a graph !
//

(function(){
  var rrdgraph = function(converter) {
	function trim_and_remove_quotes(str) {
		str = str.replace(/^[ \t]*|[ \t]*$/g, '');
		if(
				(str.startsWith("\"") && str.endsWith("\""))
				||
				(str.startsWith("'") && str.endsWith("'"))
		  ) { 
		str = str.substring(1, str.length-1); 
		}
		return(str);
	}
	function rrdgraph_escape(str, dash) {
		str = str.replace(/[\/"']/g, '_');
		if(dash) str = str.replace(/-/g, '_');
		return(str);
	}

    filter = function(text) {
		text = text.replace(/rrdgraph[ \t]*\(([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*)\)/gm, 
				function(wholeMatch, cdsrc, host, p, pi, t, ti) {
				cdsrc = rrdgraph_escape(trim_and_remove_quotes(cdsrc), false);
				host = rrdgraph_escape(trim_and_remove_quotes(host), false);
				p = rrdgraph_escape(trim_and_remove_quotes(p), true);
				pi = rrdgraph_escape(trim_and_remove_quotes(pi), false);
				t = rrdgraph_escape(trim_and_remove_quotes(t), true);
				ti = rrdgraph_escape(trim_and_remove_quotes(ti), false);
				if(cdsrc == '') return(wholeMatch);
				if(host == '') return(wholeMatch);
				if(p == '') return(wholeMatch);
				if(t == '') return(wholeMatch);

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
		return text+"<p>rrdgraph enabled</p>" ;
	};
	return ([
		{ type: 'lang', filter: filter }
	]);
  };

  // Client-side export
  if (typeof window !== 'undefined' && window.Showdown && window.Showdown.extensions) { window.Showdown.extensions.rrdgraph = rrdgraph; }
  // Server-side export
  if (typeof module !== 'undefined') {
    module.exports = rrdgraph;
  }
}());

