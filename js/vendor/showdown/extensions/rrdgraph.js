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
    filter = function(text) {
		text = text.replace(/rrdgraph[ \t]*\(([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*),([^,\)]*)\)/gm, 
				function(wholeMatch, cdsrc, host, p, pi, t, ti) {
				cdsrc = trim_and_remove_quotes(cdsrc);
				host = trim_and_remove_quotes(host);
				p = trim_and_remove_quotes(p);
				pi = trim_and_remove_quotes(pi);
				t = trim_and_remove_quotes(t);
				ti = trim_and_remove_quotes(ti);
				if(cdsrc == '') return(wholeMatch);
				if(host == '') return(wholeMatch);
				if(p == '') return(wholeMatch);
				if(t == '') return(wholeMatch);

				return("(TODO) Soon we will graph this : <ul>"
						+"<li>source = '"+cdsrc+"'</li>"
						+"<li>host = '"+host+"'</li>"
						+"<li>p = '"+p+"'</li>"
						+"<li>pi = '"+pi+"'</li>"
						+"<li>t = '"+t+"'</li>"
						+"<li>ti = '"+ti+"'</li>"
						+"</ul>"
					  );
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

