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

function get_config_cdsrc() {
    var as;
	$.ajax({
		async : false, type: 'GET', url: 'action.php',
		data : { 'action': 'get_config_list', 'tpl': 'server_collectd_source', '_': (new Date()).getTime(), 'id': 0 },
		complete : function (r) {
			if(r.status) {
				as = jQuery.parseJSON(r.responseText);
			}
		}
	});
    return(as);
}


function get_tab_id_from_name(name) {
	var pwtabid = 1;
	var result = 0;
    pwtabid = $('#itemtab li[plugin="'+name+'"]').attr('pwtabid');
    if(typeof pwtabid === 'undefined') return(0);
	return(parseInt(pwtabid));
}

function save_tab_order() {
	var tabs = $('#itemtab').tabs();
    var order = Array();
    tabs.find("li[plugin='custom_view_selection']").each(function() {
        order.push($(this).attr('custom_tab_id'));
    });
	$.ajax({
		async : false, type: "POST", url: "action.php?tpl=json_actions",
		data : { "action" : "reorder_tabs", "view_id" : view_id, "id" : json_item_datas['jstree']['id'], "order": order },
		complete : function (r) {
			if(!r.status) {
				notify_ko('Error, can\'t save data on the server !');
			}
		}
	});
}

function hide_graph_helpers() {
    $('#timebutton').hide();
    $('#datetime').hide();
    $('#timespan').hide();
}

function select_node_with_data(datas) {
    hide_graph_helpers();
	$('#itemtab').remove();
	$('#items').html('<div id="itemtab" class="no-border"></div>');
	$('#itemtab').html(ich.information_tab({ }));
	var tabs = $('#itemtab').tabs();
    $('#tabpanel').height($('#rightpane').height()-$('#itemtab ul').height() - 30);
    $('#tabadd').data('tabadd', true);
    $('#tab0').data('loaded', true);
    if(datas.length <= 0) {
        return;
    }
	var pwtabid = 1;
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
            create_plugin_tab(plugin, plugin_instance, pwtabid++);
            });
	}
	if (datas['plugins']) {
		$.each(datas['plugins'], function(plugin, plugin_instance) {
			create_plugin_tab(plugin, plugin_instance, pwtabid++);
		});
	}

    $.each(json_item_datas['tab_ids'], function(tabref, tabcontent) {
            create_custom_tab(tabref, pwtabid++, tabcontent);
            });
    tabs.tabs("option", "active", 1);
	if(id) {
		hide_menu_for(datas['jstree']['pwtype']);
	}
    tabs.tabs({
        beforeActivate: function(event, ui) {
            var pwgraph_hover_enabled_prev = pwgraph_hover_enabled;
            hide_graph_helpers();
            pwgraph_hover_enabled = false;
            if($(ui.newTab).find('a').attr('href') == '#tabadd') {
				askfornewtab(function(title, lifetime) {
					$.ajax({
						async : false, type: "POST", url: "action.php?tpl=json_actions",
						data : { "action" : "add_tab", "view_id" : view_id, "id" : json_item_datas['jstree']['id'], "tab_title" : title == '' ? 'Custom view' : title, "lifetime": lifetime },
						complete : function (r) {
							if(!r.status) {
								notify_ko('Error, can\'t retrieve data from server !');
							} else {
                                var res = jQuery.parseJSON(r.responseText);
                                var selection_id = res['selection_id'];
                                create_custom_tab('unset', pwtabid++, {'id': selection_id, 'title': title == '' ? 'Custom view' : title});
                                tabs.tabs("refresh");
							}
						}
					});
				});
                event.preventDefault();
                pwgraph_hover_enabled = pwgraph_hover_enabled_prev;
                return;
            }
            pwgraph_hover_enabled = pwgraph_hover_enabled_prev;
        },
        beforeLoad: function(event, ui) {
            if(ui.tab.data("loaded")) {
                event.preventDefault();
                return;
            }
            var pwtabid = ui.tab.attr('pwtabid');
            var tabplugin = ui.tab.attr('plugin');
            ui.panel.attr('plugin', tabplugin);
            ui.panel.attr('pwtabid', pwtabid);
            if(tabplugin == 'custom_view_selection') { ui.panel.attr('custom_tab_id', ui.tab.attr('custom_tab_id')); }
            current_tab = pwtabid;
            load_tab(pwtabid);
            ui.jqXHR.success(function() {
                ui.tab.data("loaded", true);
            });
            ui.jqXHR.abort();
        }
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
    tabs.tabs("refresh");
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
		var pwtabid = 0;
		select_node_with_data(datas);
		if(tab != "") {
			pwtabid = get_tab_id_from_name(tab);
		}
		if(pwtabid > 0) { 
            var tabs = $('#itemtab').tabs();
			current_tab = pwtabid;
            tabs.tabs('option', 'active', $('#itemtab li[plugin="'+tab+'"]').index());
            tabs.tabs("refresh");
		}
	} );
}

function select_node(nodeid) {
	$.getJSON('action.php?tpl=json_node_datas&view_id='+view_id+'&id='+nodeid, function(datas) { select_node_with_data(datas); } );
}

function create_plugin_tab(plugin, plugin_instance, pwtabid) {
    var tab_li = "<li plugin='"+plugin+"' pwtabid='"+pwtabid+"'><a href='tab"+pwtabid+"'>"+plugin+"</a></li>";
    var tabs = $('#itemtab').tabs();
    tabs.find(".ui-tabs-nav").append(tab_li);
}

function create_custom_tab(tabref, pwtabid, tabcontent) {
    var tab_li = "<li plugin='custom_view_selection' custom_tab_id='"+tabcontent['id']+"' pwtabid='"+pwtabid+"'><a href='tab"+pwtabid+"'>"+tabcontent['title']+"</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
    var tabs = $('#itemtab').tabs();
    tabs.find("#pluginselectiontabbar.ui-tabs-nav").append(tab_li);
    tabs.on("click", "li[pwtabid='"+pwtabid+"'] span.ui-icon-close", function() {
            var thistab = $(this).closest("li");
            var text = $(thistab).find('a').text();
            var custom_tab_id = $(thistab).attr('custom_tab_id');
            confirmfor({title: "Do you really want to delete tab '"+text+"'?"}, function() {
                $.ajax({
                    async : false, type: "POST", url: "action.php?tpl=json_actions",
                    data : { "action" : "del_tab", "selection_id" : custom_tab_id },
                    complete : function (r) {
                            if(!r.status) {
                                notify_ko('Error, can\'t retrieve data from server !');
                            } else {
                                var thispanel = $(thistab).attr("aria-controls");
                                $(thistab).remove();
                                $('#' + thispanel).remove();
                                tabs.tabs("refresh");
                            }
                        }
                    });
                });
            return(false);
        });
    tabs.find(".ui-tabs-nav").sortable({
                items: "li[plugin='custom_view_selection']",
                axis: 'x',
                stop: function() {
                    tabs.tabs('refresh');
                    save_tab_order();
                }
            });
}

function load_tab(pwtabid) {
    hide_graph_helpers();
	if (pwtabid == 0) { return; }
	if ($('div[pwtabid="'+pwtabid+'"]').attr('done')) {
		return;
	}
	$('div[pwtabid="'+pwtabid+'"]').attr('done', 1);
	var custom_function_test = 'typeof '+$('div[pwtabid="'+pwtabid+'"]').attr('plugin')+'_plugin_view';
	if (eval(custom_function_test) == 'function' ) {
		eval($('div[pwtabid="'+pwtabid+'"]').attr('plugin')+'_plugin_view')(pwtabid, $('div[pwtabid="'+pwtabid+'"]').attr('plugin'));
		return;
	}
	plugin_view(pwtabid, $('div[pwtabid="'+pwtabid+'"]').attr('plugin'));
}

function custom_view_selection_plugin_view(pwtabid, plugin) {
    var selection_id = $('div[pwtabid="'+pwtabid+'"]').attr('custom_tab_id');
	custom_view_selection = ich.custom_view_selection({
		pwtabid: pwtabid,
        selection_id: selection_id
	});
	$(custom_view_selection).appendTo('div[pwtabid="'+pwtabid+'"]');
}

function plugin_view (pwtabid, plugin) {
    if(! (typeof json_item_datas['aggregators'] === 'undefined')) {
        $.each(json_item_datas['aggregators'], function (cdsrc, aggregator_plugins) {
            $('<h2>Collectd "'+cdsrc+'"</h2>').appendTo('div[pwtabid="'+pwtabid+'"]');
            $.each(aggregator_plugins, function (current_plugin, current_plugin_instance) {
                if(current_plugin == plugin) {
                    $.each(current_plugin_instance, function (plugin_instance, type) {
                        $.each(type, function (type, type_instance) {
                            $.each(type_instance, function (type_instance, none) { 
                                $('<img class="graph" id="graph_'+graphid+'" zone="tab"/><br/>').appendTo('div[pwtabid="'+pwtabid+'"]');
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
    }
    if(! (typeof json_item_datas['plugins'][plugin] === 'undefined')) {
        $.each(json_item_datas['plugins'][plugin], function (plugin_instance, type) {
            $.each(type, function (type, type_instance) {
                $.each(type_instance, function (type_instance, none) { 
                    $('<img class="graph" id="graph_'+graphid+'" zone="tab"/><br/>').appendTo('div[pwtabid="'+pwtabid+'"]');
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
}

function hide_menu_for(node_type) {
	$('a[pwmenuid^="menu_"]').parent().hide();
	$('a[pwmenuid="menu_view_new"]').parent().show();
	$('a[pwmenuid="menu_view_open"]').parent().show();
	$('a[pwmenuid="menu_view_delete"]').parent().show();
	$('a[pwmenuid="menu_rename_node"]').parent().show();
	$('a[pwmenuid="menu_rename_tab"]').parent().show();
	$('a[pwmenuid="menu_copy"]').parent().show();
	$('a[pwmenuid="menu_paste"]').parent().show();
	$('a[pwmenuid="menu_cut"]').parent().show();
	$('a[pwmenuid="menu_display_toggle_tree"]').parent().show();
	$('a[pwmenuid="menu_refresh_tree"]').parent().show();
	$('a[pwmenuid="menu_refresh_status"]').parent().show();
	$('a[pwmenuid="menu_refresh_node"]').parent().show();
	$('a[pwmenuid="menu_about_box"]').parent().show();
	switch (node_type) {
		case 'server':
		case 'selection':
		break;
		case 'container':
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
		"text":text, "layout":"topCenter", "type":"error",
		"animateOpen":{"height":"toggle"}, "animateClose":{"height":"toggle"},
		"speed":500, "timeout":50000, "closeButton":false,
		"closeOnSelfClick":true, "closeOnSelfOver":true,"modal":false
	});
    $('#noty_topCenter_layout_container').css({width: "80%", left: "10%"});
    $('#noty_topCenter_layout_container li').css({width: "100%"});
}

function notify_ok(text) {
	noty({
		"text":text, "layout":"topCenter", "type":"success",
		"animateOpen":{"height":"toggle"}, "animateClose":{"height":"toggle"},
		"speed":500, "timeout":5000, "closeButton":false,
		"closeOnSelfClick":true, "closeOnSelfOver":true,"modal":false
	});
    $('#noty_topCenter_layout_container').css({width: "80%", left: "10%"});
    $('#noty_topCenter_layout_container li').css({width: "100%"});
}
function showserverlist(list, title, state) {
    $('<div id="modaldialogcontents"></div>').dialog({
            autoOpen: true,
            title: title,
            height: 'auto',
            width: 'auto',
            position: {my: 'left top', at: 'left+12 top+64', of: '#items' },
            appendTo: '#modaldialog',
            buttons: {
                    Ok: function() {
                        $(this).dialog('close');
                    }
                },
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html('<textarea class="'+state+'"id="serverstatuslist">'+list+'</textarea>');
            }
        });
}
function askfornewtab(func) {
    $('<div id="modaldialogcontents"></div>').dialog({
            autoOpen: true,
            title: 'New tab',
            height: 'auto',
            width: 'auto',
            appendTo: '#modaldialog',
            buttons: {
                Ok: function() {
                        var askfornewtabname = $('#askfornewtabname').val();
                        var askfornewtabttl = $('#askfornewtabttl').val();
                        $(this).dialog('close');
                        func(askfornewtabname, askfornewtabttl);
                    },
                Cancel: function() {
                        $(this).dialog('close');
                    }
            },
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html(ich.newtabtpl());
            }
        });
  return false;
}
function askfor(optionsarg, func) {
	var options = { cancellabel: 'Cancel', oklabel: 'Ok', title: 'Question'};
	$.extend(options, optionsarg);
    $('<div id="modaldialogcontents"></div>').dialog({
            autoOpen: true,
            title: options['title'],
            height: 'auto',
            width: 'auto',
            position: {my: 'center top', at: 'center top+64', of: '#items' },
            appendTo: '#modaldialog',
            buttons: [
                {
                    text: options['oklabel'],
                    click: function() {
                        var askforvalue = $('#askforvalue').val();
                        $(this).dialog('close');
                        func(askforvalue);
                    }
                },
                {
                    text: options['cancellabel'],
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ],
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html(ich.askfor({label: options['title']}));
            }
        });
  return false;
}

function confirmfor(optionsarg, func) {
	var options = { cancellabel: 'Cancel', oklabel: 'Ok', title: 'Question'};
	$.extend(options, optionsarg);
    $('<div id="modaldialogcontents"></div>').dialog({
            autoOpen: true,
            title: options['title'],
            height: 'auto',
            width: 'auto',
            position: {my: 'center top', at: 'center top+64', of: '#items' },
            appendTo: '#modaldialog',
            buttons: [
                {
                    text: options['oklabel'],
                    click: function() {
                        $(this).dialog('close');
                        func();
                    }
                },
                {
                    text: options['cancellabel'],
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ],
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html(ich.confirmfor({label: options['title']}));
            }
        });
  return false;
}

function perfwatcher_about_box() {
//	TODO : use ICanHaz here ?
    var pwgraph_hover_enabled_prev = pwgraph_hover_enabled;
    hide_graph_helpers();
    pwgraph_hover_enabled = false;
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
                pwgraph_hover_enabled = pwgraph_hover_enabled_prev;
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html(ich.version({ 'version': PERFWATCHER_VERSION}));
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
	if(i<0) { i = 0; }
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
    var pwgraph_hover_enabled_prev = pwgraph_hover_enabled;
    hide_graph_helpers();
    pwgraph_hover_enabled = false;
    $('<div id="modaldialogcontents"></div>')
        .html(
                '<div id="viewgrid">'
                +'  <table id="viewtable"></table>'
                +'  <div id="viewdiv"></div>'
                +'</div>'
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
                pwgraph_hover_enabled = true;
            },
            open: function(event, ui) {
                $('#modaldialog').show();
            	var url = 'action.php?tpl=json_actions&action=list_views';
                $('#viewtable').jqGrid({
                    url: url,
                    datatype: "json",
                    colNames: ['ID', 'View name'],
                    colModel: [
                        {name: 'view_id', index: 'view_id', hidden: true},
                        {name: 'title', index: 'title'}
                        ],
                    rowNum: 20,
                    rowList: [10,20,30],
                    height: 'auto',
                    width: 520,
                    loadonce: true,
                    caption: "",
                    onSelectRow: function(id) {
                        var rowdata = $('#viewtable').jqGrid('getRowData', id);
                        $('#modaldialogcontents').dialog('close');
                        if(rowdata.view_id > 0) {
                            view_id = rowdata.view_id;
                            set_view();
                        }
                    }
                });
                $('#viewtable').jqGrid('filterToolbar', {searchOperators: false, searchOnEnter: false, defaultSearch: 'cn'});
                $('#viewgrid tr.ui-jqgrid-labels').hide();
            }
        })
        .show();
}

function ajax_download(url, data) {
// Code from http://stackoverflow.com/questions/4545311/download-a-file-by-jquery-ajax/21223167#21223167
    var $iframe, iframe_doc, iframe_html;

    if (($iframe = $('#download_iframe')).length === 0) {
        $iframe = $("<iframe id='download_iframe' name='download_iframe'" +
                    " style='display: none' src='about:blank'></iframe>"
                   ).appendTo("body");
    }

    iframe_doc = $iframe[0].contentWindow || $iframe[0].contentDocument;
    if (iframe_doc.document) {
            iframe_doc = iframe_doc.document;
    }

    iframe_html = "<html><head></head><body><form method='POST' action='" + url +"'>";

    Object.keys(data).forEach(function(key){
        iframe_html += "<input type='hidden' name='"+key+"' value='"+data[key]+"'>";
                            });

    iframe_html +="</form></body></html>";

    iframe_doc.open();
    iframe_doc.write(iframe_html);
    $(iframe_doc).find('form').submit();
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
