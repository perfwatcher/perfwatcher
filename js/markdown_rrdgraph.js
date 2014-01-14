/**
 * Helpers for markdown for Perfwatcher
 * rrdgraph(source,host,plugin,plugin_instance,type,type_instance) -> a graph !
 *
 * Showdown extension follows.
 *
 * Copyright (c) 2013 Yves METTIER
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
 * @author    Yves Mettier <ymettier at free fr>
 * @copyright 2013 Yves Mettier
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/

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
			if(pi === '_') pi = '';
			if(ti === '_') ti = '';

			var src = JSON.stringify({ 
					"cdsrc": cdsrc,
					"host": host,
					"plugin": p,
					"plugininstance": pi,
					"type": t,
					"typeinstance": ti
			});
			return('<span class="rrdgraph_to_render" rrdgraph="'+encodeURIComponent(src)+'"></span>');
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

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
