
function select_node_with_data(datas) {
	$('#timebutton').hide();
	$('#datetime').hide();
	$('#timespan').hide();
	$('#itemtab').remove();
	$('#items').html('<div id="itemtab"></div>');
	$('#itemtab').html(ich.information_tab({ }));
	$('#itemtab').jqxTabs({ height: $('#mainSplitter').height() -3, theme: theme, scrollStep: 697 });
	var tabid = 1;
	json_item_datas = datas;
	var id;
	if(datas['jstree'] && datas['jstree']['id']) {
		id = datas['jstree']['id'];
	}
	if(id) {
		$.ajax({
			async : false, type: 'POST', url: "action.php?tpl=get_hosts",
			data : { 
				"view_id" : view_id,
				"id" : json_item_datas['jstree']['id']
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
	} else {
			json_item_datas['hosts'] = [ json_item_datas['host'] ];
	}
	if (datas['plugins']) {
		$.each(datas['plugins'], function(plugin, plugin_instance) {
			create_plugin_tab(plugin, plugin_instance, tabid++);
		});
	}
	if (datas['datas'] && datas['datas']['tabs']) {
		$.each(datas['datas']['tabs'], function(tabref, tabcontent) {
	    	create_custom_tab(tabref, tabid++);
		});
	}
	$('#itemtab').jqxTabs('select', 0);
	if(id) {
		hide_menu_for(datas['jstree']['type']);
	}
	$('#itemtab').bind('tabclick', function (event) {
		current_tab = event.args.item;
		load_tab(event.args.item);
	});
	if(json_item_datas['config'] && json_item_datas['config']['widgets']) {
	var panel = 0;
	var i = 0;
	$.each(json_item_datas['config']['widgets'], function (widget_name, widget_datas) {
		$(ich.widget({
			widget_id : i,
			widget : widget_name,
			widget_title : widget_datas['title'],
		})).appendTo('#infodockpanel'+panel);
		$('#widget_content'+i).load(widget_datas['content_url']);
		if (panel++ > 0) { panel = 0; }
		i++;
	});
	}
	$('#infodock').jqxDocking({
		theme: theme,
		orientation: 'horizontal',
		mode: 'docked'
	});
}

function select_node_by_name(host) {
	$.getJSON('action.php?tpl=json_node_defaults&view_id='+view_id+'&host='+host, function(datas) { select_node_with_data(datas); } );
}

function select_node(nodeid) {
	$.getJSON('action.php?tpl=json_node_datas&view_id='+view_id+'&id='+nodeid, function(datas) { select_node_with_data(datas); } );
}

function create_plugin_tab(plugin, plugin_instance, tabid) {
	$('#itemtab').jqxTabs('addAt', tabid, plugin, '<div plugin="'+plugin+'" tabid="'+tabid+'"></div>');
}

function create_custom_tab(tabref, tabid) {
	$('#itemtab').jqxTabs('addAt', tabid, json_item_datas['datas']['tabs'][tabref]['tab_title'], '<div plugin="custom_view_'+json_item_datas['jstree']['type']+'" custom_tab_id="'+tabref+'" tabid="'+tabid+'"></div>');
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

function custom_view_default_plugin_view(tabid, plugin) {
	custom_view_default = ich.custom_view_default({
		tabid: tabid
	});
	$(custom_view_default).appendTo('div[tabid="'+tabid+'"]');
}

function custom_view_folder_plugin_view(tabid, plugin) {
	custom_view_folder = ich.custom_view_folder({
		tabid: tabid
	});
	$(custom_view_folder).appendTo('div[tabid="'+tabid+'"]');
}

function plugin_view (tabid, plugin) {
	$.each(json_item_datas['plugins'][plugin], function (plugin_instance, type) {
		$.each(type, function (type, type_instance) {
			$.each(type_instance, function (type_instance, none) { 
				$('<img class="graph" id="graph_'+graphid+'"/><br/>').appendTo('div[tabid="'+tabid+'"]');
				$('#graph_'+graphid).pwgraph({
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
	$('li[id^="menu_"]').hide();
	$('li[id="menu_new_tab"]').show();
	$('li[id="menu_view_new"]').show();
	$('li[id="menu_view_open"]').show();
	$('li[id="menu_view_delete"]').show();
	$('li[id="menu_rename_node"]').show();
	$('li[id="menu_rename_tab"]').show();
	$('li[id="menu_delete_tab"]').show();
	$('li[id="menu_delete_node"]').show();
	$('li[id="menu_copy"]').show();
	$('li[id="menu_paste"]').show();
	$('li[id="menu_cut"]').show();
	$('li[id="menu_display_toggle_tree"]').show();
	$('li[id="menu_display_in_new_window"]').show();
	$('li[id="menu_refresh_tree"]').show();
	$('li[id="menu_refresh_status"]').show();
	$('li[id="menu_refresh_node"]').show();
	$('li[id="menu_about_box"]').show();
	switch (node_type) {
		case 'default':
		break;
		case 'folder':
		case 'drive':
			$('li[id="menu_new_server"]').show();
			$('li[id="menu_new_container"]').show();
			$('li[id="menu_new_aggregator"]').show();
			$('li[id="menu_configure"]').show();
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
    var hosts = new Array();
    $('li[id^="node_"]').each(function(index, element) {
            if ($('#'+this.id).attr('rel') != 'drive' && $('#'+this.id).attr('rel') != 'folder') {
				var host = $('#'+this.id+' a').html().substr(37);
				$('#'+this.id).attr('host', host);
                hosts.push(host);
            }
        }
    );
    $.ajax({
        async : true,
        type: 'POST',
        url: "json-rpc",
		data: JSON.stringify({"jsonrpc": "2.0", "method": "pw_get_status", "params": { "timeout": 240, "server": hosts}, "id": 0}),
        dataType : 'json',
        complete : function (r) {
            if(r.status) {
                var res = jQuery.parseJSON(r.responseText);
				for(var host in res['result']) {
					switch(res['result'][host]) {
						case 'up':
							$('li[id^="node_"][host="'+host+'"]').attr('rel', 'default-green');
						break;
						case 'down':
							$('li[id^="node_"][host="'+host+'"]').attr('rel', 'default-red');
						break;
						case 'unknown':
							$('li[id^="node_"][host="'+host+'"]').attr('rel', 'default-grey');
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
	  $('#modalwindow').jqxWindow({ height: 287, width: 262, title: 'About Perfwatcher', isModal: true, theme: theme }).show();
	  $('#modalwindowcontent').html('<p>About Perfwatcheur</p>');
		$.ajax({
			async : false, type: 'POST', url: "action.php?tpl=version",
			complete : function (r) {
				if(r.status) {
					$('#modalwindowcontent').html(r.responseText);
				}
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
	$('#modalwindow').jqxWindow({ title: '<span id="toptitle">Select a view</span>', isModal: false, theme: theme, width: 537, height: 600 }).show();
	$('#modalwindowcontent').html(
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
		);
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
		$('#modalwindow').jqxWindow('closeWindow');
		if(me.view_id > 0) {
			view_id = me.view_id;
			set_view();
		}
	});
	$('#select_view_button_cancel').on('click', function(event) {
		$('#modalwindow').jqxWindow('closeWindow');
	});
//	set_view();
}
