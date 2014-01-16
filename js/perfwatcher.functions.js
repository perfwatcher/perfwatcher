/**
 * Generic helper functions for Perfwatcher
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

function get_grouped_types() {
    var gp;
	$.ajax({
		async : false, type: 'GET', url: 'action.php',
		data : { 'action': 'get_grouped_type', 'tpl': 'json_actions', '_': (new Date()).getTime(), 'id': 0 },
		complete : function (r) {
			if(r.status) {
				gp = jQuery.parseJSON(r.responseText);
			}
		}
	});
    return(gp);
}

function get_tab_id_from_name(name) {
	var tabid = 1;
	var result = 0;
    tabid = $('#items div[plugin="'+name+'"]').attr('tabid');
    if(typeof tabid === 'undefined') return(0);
	return(parseInt(tabid));
}

function select_node_with_data(datas) {
	$('#timebutton').hide();
	$('#datetime').hide();
	$('#timespan').hide();
	$('#itemtab').remove();
	$('#items').html('<div id="itemtab"></div>');
	$('#itemtab').html(ich.information_tab({ }));
	$('#itemtab').jqxTabs({ height: $('#mainSplitter').height() -3, theme: theme, scrollStep: 697, keyboardNavigation: false });
    if(datas.length <= 0) {
        return;
    }
	var tabid = 1;
	json_item_datas = datas;
	var id;
	if(datas['jstree'] && datas['jstree']['id']) {
		id = datas['jstree']['id'];
	}
	if(id) {
		$.ajax({
			async : false, type: 'POST', url: "action.php?tpl=json_actions&action=get_hosts",
			data : { 
				"view_id" : view_id,
				"id" : id
			},
			complete : function (r) {
				if(r.status) {
					hosts = jQuery.parseJSON(r.responseText);
					if(hosts.length == 0) {
						hosts = [json_item_datas['host']];
					}
					json_item_datas['hosts'] = hosts;
				}
			}
		});
		$('[tag="hostname"] b').html(datas['jstree']['title']);

		$.ajax({
			async : false, type: 'POST', url: "action.php?tpl=json_actions&action=get_tabs",
			data : { 
				"view_id" : view_id,
				"id" : id
			},
			complete : function (r) {
				if(r.status) {
					tab_ids = jQuery.parseJSON(r.responseText);
					if(tab_ids.length == 0) {
						tab_ids = [];
					}
					json_item_datas['tab_ids'] = tab_ids;
				}
			}
		});
	} else {
			json_item_datas['hosts'] = [ json_item_datas['host'] ];
            json_item_datas['tab_ids'] = [];
	}
	if (datas['aggregators']) {
        agg = {};
        $.each(datas['aggregators'], function(cdsrc, aggregator) {
            $.each(aggregator, function(plugin, plugin_instance) {
                    agg[plugin] = plugin_instance;
                });
            });
        $.each(agg, function(plugin, plugin_instance) {
            create_plugin_tab(plugin, plugin_instance, tabid++);
            });
	}
	if (datas['plugins']) {
		$.each(datas['plugins'], function(plugin, plugin_instance) {
			create_plugin_tab(plugin, plugin_instance, tabid++);
		});
	}

    $.each(json_item_datas['tab_ids'], function(tabref, tabcontent) {
            create_custom_tab(tabref, tabid++, tabcontent);
            });
	$('#itemtab').jqxTabs('select', 0);
	if(id) {
		hide_menu_for(datas['jstree']['pwtype']);
	}
	$('#itemtab').bind('tabclick', function (event) {
		current_tab = event.args.item;
		load_tab(event.args.item);
	});
/* Portlets */
	if(json_item_datas['config'] && json_item_datas['config']['widgets']) {
/* Portlets configuration */
	var panel = 0;
	var i = 0;
	$.each(json_item_datas['config']['widgets'], function (widget_name, widget_datas) {
		$(ich.widget({
			widget_id : i,
			widget : widget_name,
			widget_title : widget_datas['title'],
		})).appendTo('#column'+panel);
 
		$('#widget_content'+i+" div").load(widget_datas['content_url']);
		if (panel++ > 0) { panel = 0; }
		i++;
	});

/* Portlets initialization */
	$( ".column" ).sortable({
		connectWith: ".column"
	});

	$( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
			.find( ".portlet-header" )
			.addClass( "ui-widget-header ui-corner-all" )
			.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
			.end()
		.find( ".portlet-content" )
			.end()
		.find( ".portlet-content div" )
			.removeClass()
			.addClass("widget-contents")
			.end();
	$( ".portlet-header .ui-icon" ).click(function() {
			$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
			$( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
	});

	}
}


function select_node_by_name(fullhost) {
    var a = fullhost.split("/");
    var collectd_source = "";
    var host;
    var tab;
    if(a[0] == "") {
        // Syntax "/collectd_source/host/tab"
        collectd_source = a[1];
        host = a[2];
        tab = a[3];
    } else {
        // Syntax "host/tab"
        host = a[0];
        tab = a[1];
    }
	$.getJSON('action.php?tpl=json_node_defaults&view_id='+view_id+'&CdSrc='+collectd_source+'&host='+host, function(datas) {
		var tabid = 0;
		select_node_with_data(datas);
		if(tab != "") {
			tabid = get_tab_id_from_name(tab);
		}
		if(tabid > 0) { 
			current_tab = tabid;
			load_tab(tabid);
			$('#itemtab').jqxTabs('select', tabid);
		}
	} );
}

function select_node(nodeid) {
	$.getJSON('action.php?tpl=json_node_datas&view_id='+view_id+'&id='+nodeid, function(datas) { select_node_with_data(datas); } );
}

function create_plugin_tab(plugin, plugin_instance, tabid) {
	$('#itemtab').jqxTabs('addAt', tabid, plugin, '<div plugin="'+plugin+'" tabid="'+tabid+'"></div>');
}

function create_custom_tab(tabref, tabid, tabcontent) {
	$('#itemtab').jqxTabs('addAt', tabid,
            tabcontent['title'],
            '<div plugin="custom_view_selection" custom_tab_id="'+tabcontent['id']+'" tabid="'+tabid+'"></div>'
            );
}

function load_tab(tabid) {
	$('#timebutton').hide();
	$('#datetime').hide();
	$('#timespan').hide();
	if (tabid == 0) { return; }
	if ($('div[tabid="'+tabid+'"]').attr('done')) {
		return;
	}
	$('div[tabid="'+tabid+'"]').attr('done', 1);
	var custom_function_test = 'typeof '+$('div[tabid="'+tabid+'"]').attr('plugin')+'_plugin_view';
	if (eval(custom_function_test) == 'function' ) {
		eval($('div[tabid="'+tabid+'"]').attr('plugin')+'_plugin_view')(tabid, $('div[tabid="'+tabid+'"]').attr('plugin'));
		return;
	}
	plugin_view(tabid, $('div[tabid="'+tabid+'"]').attr('plugin'));
}

function custom_view_selection_plugin_view(tabid, plugin) {
    var selection_id = $('div[tabid="'+tabid+'"]').attr('custom_tab_id');
	custom_view_selection = ich.custom_view_selection({
		tabid: tabid,
        selection_id: selection_id
	});
	$(custom_view_selection).appendTo('div[tabid="'+tabid+'"]');
}

function plugin_view (tabid, plugin) {
	$.each(json_item_datas['aggregators'], function (cdsrc, aggregator_plugins) {
        $('<h2>Collectd "'+cdsrc+'"</h2>').appendTo('div[tabid="'+tabid+'"]');
		$.each(aggregator_plugins, function (current_plugin, current_plugin_instance) {
            if(current_plugin == plugin) {
				$.each(current_plugin_instance, function (plugin_instance, type) {
					$.each(type, function (type, type_instance) {
						$.each(type_instance, function (type_instance, none) { 
							$('<img class="graph" id="graph_'+graphid+'" zone="tab"/><br/>').appendTo('div[tabid="'+tabid+'"]');
							$('#graph_'+graphid).pwgraph({
								cdsrc: cdsrc,
								host: json_item_datas['host'],
								plugin: plugin,
								plugin_instance: plugin_instance,
								type: type,
								type_instance: type_instance
							}).pwgraph('display');
							graphid++;
						});
					});
				});
            }
		});
	 });
	$.each(json_item_datas['plugins'][plugin], function (plugin_instance, type) {
		$.each(type, function (type, type_instance) {
			$.each(type_instance, function (type_instance, none) { 
				$('<img class="graph" id="graph_'+graphid+'" zone="tab"/><br/>').appendTo('div[tabid="'+tabid+'"]');
				$('#graph_'+graphid).pwgraph({
					cdsrc: json_item_datas['config']['CdSrc']['source'],
					host: json_item_datas['host'],
					plugin: plugin,
					plugin_instance: plugin_instance,
					type: type,
					type_instance: type_instance
				}).pwgraph('display');
				graphid++;
			});
		});
	 });
}

function hide_menu_for(node_type) {
	$('a[pwmenuid^="menu_"]').parent().hide();
	$('a[pwmenuid="menu_view_new"]').parent().show();
	$('a[pwmenuid="menu_view_open"]').parent().show();
	$('a[pwmenuid="menu_view_delete"]').parent().show();
	$('a[pwmenuid="menu_rename_node"]').parent().show();
	$('a[pwmenuid="menu_rename_tab"]').parent().show();
	$('a[pwmenuid="menu_delete_tab"]').parent().show();
	$('a[pwmenuid="menu_delete_node"]').parent().show();
	$('a[pwmenuid="menu_copy"]').parent().show();
	$('a[pwmenuid="menu_paste"]').parent().show();
	$('a[pwmenuid="menu_cut"]').parent().show();
	$('a[pwmenuid="menu_display_toggle_tree"]').parent().show();
	$('a[pwmenuid="menu_display_in_new_window"]').parent().show();
	$('a[pwmenuid="menu_refresh_tree"]').parent().show();
	$('a[pwmenuid="menu_refresh_status"]').parent().show();
	$('a[pwmenuid="menu_refresh_node"]').parent().show();
	$('a[pwmenuid="menu_about_box"]').parent().show();
	switch (node_type) {
		case 'server':
		case 'selection':
            $('a[pwmenuid="menu_new_tab"]').parent().show();
		break;
		case 'container':
			$('a[pwmenuid="menu_new_server"]').parent().show();
			$('a[pwmenuid="menu_new_container"]').parent().show();
			$('a[pwmenuid="menu_new_aggregator"]').parent().show();
			$('a[pwmenuid="menu_configure"]').parent().show();
		break;
	}
}

function reload_datas() {
	$.ajax({
	    async : false,
	    type: 'POST',
	    url: 'action.php?tpl=json_node_datas&view_id='+view_id+'&id='+json_item_datas['jstree']['id'],
	    complete : function (r) {
	        if(r.status) {
				json_item_datas = jQuery.parseJSON(r.responseText);
	        }
	    }
	});
}


function auto_refresh_status() {
	refresh_status();
	window.setTimeout(function () {
	        auto_refresh_status();
    }, 10000);
}

function refresh_status() {
    var cdsrc_hosts = {};
    var hosts = new Array();
    $('li[id^="node_"]').each(function(index, element) {
            cdsrc_hosts[ $('#'+this.id).attr('CdSrc') ] = 1;
        }
    );
    $.each(cdsrc_hosts, function(cdsrc, useless) {
            $('li[id^="node_"][CdSrc="'+cdsrc+'"]').each(function(index, element) {
                if ($('#'+this.id).attr('rel') == 'default') {
                    var host = $('#'+this.id+' a').html().substr(37);
                    $('#'+this.id).attr('host', host);
                    hosts.push(host);
                }
            });
            if(hosts.length > 0) {
                $.ajax({
                    async : true,
                    type: 'POST',
                    url: "action.php?tpl=jsonrpc&cdsrc="+cdsrc,
                    data: JSON.stringify({"jsonrpc": "2.0", "method": "pw_get_status", "params": { "timeout": 240, "server": hosts}, "id": 0}),
                    dataType : 'json',
                    complete : function (r) {
                        if(r.status) {
                            var res = jQuery.parseJSON(r.responseText);
                            for(var host in res['result']) {
                                switch(res['result'][host]) {
                                    case 'up':
                                        $('li[id^="node_"][host="'+host+'"][CdSrc="'+cdsrc+'"]').attr('rel', 'default-green');
                                        break;
                                    case 'down':
                                        $('li[id^="node_"][host="'+host+'"][CdSrc="'+cdsrc+'"]').attr('rel', 'default-red');
                                        break;
                                    case 'unknown':
                                        $('li[id^="node_"][host="'+host+'"][CdSrc="'+cdsrc+'"]').attr('rel', 'default-grey');
                                        break;
                                }
                            }
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                               var error =  jQuery.parseJSON(XMLHttpRequest['responseText']);
                               notify_ko('jsonrpc error : '+error['error']['message']+' (code : '+error['error']['code']+')');
                            },
                });
            }
    });
}

function notify_ko(text) {
	noty({
		"text":text, "layout":"center", "type":"error",
		"animateOpen":{"height":"toggle"}, "animateClose":{"height":"toggle"},
		"speed":500, "timeout":5000, "closeButton":false,
		"closeOnSelfClick":true, "closeOnSelfOver":true,"modal":false
	});
}

function notify_ok(text) {
	noty({
		"text":text, "layout":"center", "type":"success",
		"animateOpen":{"height":"toggle"}, "animateClose":{"height":"toggle"},
		"speed":500, "timeout":5000, "closeButton":false,
		"closeOnSelfClick":true, "closeOnSelfOver":true,"modal":false
	});
}
function showserverlist(list, type) {
	noty({
		"text":'<textarea>'+list+'</textarea>', "layout":"center", "type":type,
		"animateOpen":{"height":"toggle"}, "animateClose":{"height":"toggle"},
		"speed":500, "timeout":60000, "closeButton":true,
		"closeOnSelfClick":false, "closeOnSelfOver":false,"modal":true
	});
}
function askfornewtab(optionsarg, func) {
	var options = { cancellabel: 'Cancel', oklabel: 'Create'};
	$.extend(options, optionsarg);
  noty({
	"layout":"center",
    text: 'Enter a name for this new tab <input type="text" id="askforinput" value="">  and a lifetime <select id="askforinput2" ><option value="0">Infinite</option><option value="86400">1 day</option><option value="604800">7 days</option><option value="2678400">1 month</option></select>', 
    buttons: [
      {type: 'button green', text: options['oklabel'], click: function($noty) {
	  	  var name = $('#askforinput').val();
		  func($('#askforinput').val(), $('#askforinput2').val());
          $noty.close();
        }
      },
      {type: 'button pink', text: options['cancellabel'], click: function($noty) {
          $noty.close();
          noty({force: true, text: 'You clicked "'+options['cancellabel']+'" button', type: 'error', "layout":"center", "closeOnSelfClick":true, "closeOnSelfOver":true});
        }
      }
      ],
    closable: false,
    timeout: false
  });
  return false;
}
function askfor(optionsarg, func) {
	var options = { cancellabel: 'Cancel', oklabel: 'Ok', title: 'How mutch ?'};
	$.extend(options, optionsarg);
  noty({
	"layout":"center",
    text: options['title']+' <input type="text" id="askforinput" value="">', 
    buttons: [
      {type: 'button green', text: options['oklabel'], click: function($noty) {
	  	  var name = $('#askforinput').val();
		  func($('#askforinput').val());
          $noty.close();
        }
      },
      {type: 'button pink', text: options['cancellabel'], click: function($noty) {
          $noty.close();
          noty({force: true, text: 'You clicked "'+options['cancellabel']+'" button', type: 'error', "layout":"center", "closeOnSelfClick":true, "closeOnSelfOver":true});
        }
      }
      ],
    closable: false,
    timeout: false
  });
  return false;
}

function confirmfor(optionsarg, func) {
  var options = { cancellabel: 'Cancel', oklabel: 'Ok', title: 'How mutch ?'};
  $.extend(options, optionsarg);
  noty({
	"layout":"center",
    text: options['title'], 
    buttons: [
      {type: 'button green', text: options['oklabel'], click: function($noty) {
		  func();
          $noty.close();
        }
      },
      {type: 'button pink', text: options['cancellabel'], click: function($noty) {
          $noty.close();
          noty({force: true, text: 'You clicked "'+options['cancellabel']+'" button', type: 'error', "layout":"center", "closeOnSelfClick":true, "closeOnSelfOver":true});
        }
      }
      ],
    closable: false,
    timeout: false
  });
  return false;
}

function perfwatcher_about_box() {
//	TODO : use ICanHaz here ?
    $('<div id="modaldialogcontents"></div>')
        .html('<p>About Perfwatcher...</p>')
        .dialog({
            autoOpen: true,
            appendTo: '#modaldialog',
            title: 'About Perfwatcher',
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $.ajax({
                    async : false, type: 'POST', url: "action.php?tpl=version",
                    complete : function (r) {
                        if(r.status) {
                            $('#modaldialogcontents').html(r.responseText);
                        }
                    }
                });
            }
        });
}

function isRightClick(event) {
	var rightclick;
	if (!event) var event = window.event;
	if (event.which) rightclick = (event.which == 3);
	else if (event.button) rightclick = (event.button == 2);
	return rightclick;
}

// Author: various
// Source: https://gist.github.com/mathiasbynens/326491
// Licence: public domain ?
jQuery.fn.insertAtCaret = function(myValue) {
    return this.each(function() {
        var me = this;
        if (document.selection) { // IE
            me.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
            me.focus();
        } else if (me.selectionStart || me.selectionStart == '0') { // Real browsers
            var startPos = me.selectionStart, endPos = me.selectionEnd, scrollTop = me.scrollTop;
            me.value = me.value.substring(0, startPos) + myValue + me.value.substring(endPos, me.value.length);
            me.focus();
            me.selectionStart = startPos + myValue.length;
            me.selectionEnd = startPos + myValue.length;
            me.scrollTop = scrollTop;
        } else {
            me.value += myValue;
            me.focus();
        }
    });
};

// Author:  Jacek Becela
// Source:  http://gist.github.com/399624
// License: MIT
jQuery.fn.single_double_click = function(single_click_callback, double_click_callback, timeout) {
  return this.each(function(){
    var clicks = 0, self = this;
    jQuery(this).click(function(event){
      clicks++;
      if (clicks == 1) {
        setTimeout(function(){
          if(clicks == 1) {
            single_click_callback.call(self, event);
          } else {
            double_click_callback.call(self, event);
          }
          clicks = 0;
        }, timeout || 300);
      }
    });
  });
}

// Grabed from http://codeaid.net/javascript/convert-size-in-bytes-to-human-readable-format-(javascript)
function bytesToSize(bytes) {
    var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
	if (bytes == 0) return 'n/a';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

function splitMetric (metric) {
	var p;
	var pi;
	var t;
	var ti;
	g = metric.split('/')[0];
	d = metric.split('/')[1];
	if (g.indexOf('-') == -1) {
		p = g;
		pi = '';
	} else {
		p = g.substring(0, g.indexOf('-'));
		pi = g.substring(g.indexOf('-') + 1);
	}
	if (d.indexOf('-') == -1) {
		t = d;
		ti = '';
	} else {
		t = d.substring(0, d.indexOf('-'));
		ti = d.substring(d.indexOf('-') + 1);
	}
	
	return [p, pi, t, ti];
}

function select_view (set_view) {
//	TODO : use ICanHaz here
    $('<div id="modaldialogcontents"></div>')
        .html(
        		'<div>'+
        			'<span style="float: left; margin-top: 5px; margin-right: 4px;">View :</span>'+
        			'<input class="jqx-input" id="select_view_search" type="text" style="height: 23px; float: left; width: 223px;" />'+
        		'</div>'+
        		'<div style="clear: both;"></div>'+
        		'<div id="select_view_list" style="margin-top: 10px;"></div>'+
        		'<div style="clear: both;"></div>'+
        		'<div style="float: right;">'+
        			'<input type="button" value="No view selected" id="select_view_button_ok" />'+
        			'<input type="button" value="Cancel" id="select_view_button_cancel" />'+
        		'</div>'
             )
        .dialog({
            autoOpen: true,
            width: 537,
            maxHeight: $(window).height() - 50,
            position: {my: 'center top', at: 'center top', of: '#items' },
            appendTo: '#modaldialog',
            title: 'Select a view',
            close: function(event,ui) {
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                $(this).dialog('destroy').remove();
            },
            open: function(event, ui) {
                $('#modaldialog').show();
            	var url = 'action.php?tpl=json_actions&action=list_views';
            	var source = { 
            		datatype: "json", 
            		datafields: [ 
            			{ name: 'view_id' }, 
            			{ name: 'title' }
            		], 
            		id: 'id', 
            		url: url,
            		data: {
            			maxrows: '10'
            		}
            	};
            	var dataAdapter = new $.jqx.dataAdapter(source, {
            		formatData: function(data) {
            			data.startswith = $("#select_view_search").val();
            			return data;
            		}
            	});
            	$('#select_view_list').jqxListBox({
            		width: 525,
            		height: 500,
            	    source: dataAdapter,
            		displayMember: 'title',
            		valueMember: 'view_id',
            	    theme: theme
            	});
            	var me = this;
            	me.view_id = 0;
            	$('#select_view_search').on('keyup', function(event) {
            		if(me.timer) clearTimeout(me.timer);
            		me.timer = setTimeout(function() {
            			dataAdapter.dataBind();
            		}, 300);
            	});
            	$('#select_view_list').on('select', function(event) {
            		var item = event.args.item;
            		if(item) {
            			me.view_id = item.value;
            			$('#select_view_button_ok').val('Load view "'+item.label+'"');
            		}
            	});
            	$('#select_view_button_ok').jqxButton({ theme: theme, width: '150', height: '25' });
            	$('#select_view_button_cancel').jqxButton({ theme: theme, width: '150', height: '25' });
            
            	$('#select_view_button_ok').on('click', function(event) {
            		$('#modaldialogcontents').dialog('close');
            		if(me.view_id > 0) {
            			view_id = me.view_id;
            			set_view();
            		}
            	});
            	$('#select_view_button_cancel').on('click', function(event) {
            		$('#modaldialogcontents').dialog('close');
            	});
            }
        })
        .show();
//	set_view();
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
