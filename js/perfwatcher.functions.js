
		function select_node(nodeid) {
			$('#timebutton').hide();
			$('#datetime').hide();
			$('#itemtab').remove();
			$('#items').html('<div id="itemtab"></div>');
			$('#itemtab').html(ich.information_tab({ }));
			$('#itemtab').jqxTabs({ height: $('#mainSplitter').height() -3, theme: theme, scrollStep: 697 });
			$.getJSON('action.php?tpl=json_plugins_list&id='+nodeid, function(datas) {
				var tabid = 1;
				json_item_datas = datas;
				//console.log(json_item_datas);
				$('[tag="hostname"] b').html(datas['jstree']['title']);
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
				hide_menu_for(datas['jstree']['type']);
				$('#itemtab').bind('tabclick', function (event) {
					current_tab = event.args.item;
					load_tab(event.args.item);
				});
			});
			for (i=panel=0; i < 6; i++) {
				$(ich.widget({
					widget_id : i,
					widget_title : 'title n°'+i,
					widget_content : 'content n°'+i
				})).appendTo('#infodockpanel'+panel);
				if (panel++ > 2) { panel = 0; }
			}
			$('#infodock').jqxDocking({
				theme: theme,
				orientation: 'horizontal',
				mode: 'docked'
			});
		}

		function create_plugin_tab(plugin, plugin_instance, tabid) {
			$('#itemtab').jqxTabs('addAt', tabid, plugin, '<div plugin="'+plugin+'" tabid="'+tabid+'"></div>');
		}

		function create_custom_tab(tabref, tabid) {
			$('#itemtab').jqxTabs('addAt', tabid, json_item_datas['datas']['tabs'][tabref]['tab_title'], '<div plugin="custom_view_'+json_item_datas['jstree']['type']+'" custom_tab_id="'+tabref+'" tabid="'+tabid+'"></div>');
		}


		function load_tab(tabid) {
			if (tabid == 0) { return; }
			$('#timebutton').hide();
			$('#datetime').hide();
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
			$('li[id="menu_rename_node"]').show();
			$('li[id="menu_rename_tab"]').show();
			$('li[id="menu_delete_tab"]').show();
			$('li[id="menu_delete_node"]').show();
			$('li[id="menu_copy"]').show();
			$('li[id="menu_paste"]').show();
			$('li[id="menu_cut"]').show();
			$('li[id="menu_view_toogle_tree"]').show();
			$('li[id="menu_view_openinnewwindow"]').show();
			$('li[id="menu_refresh_tree"]').show();
			$('li[id="menu_refresh_status"]').show();
			$('li[id="menu_refresh_node"]').show();
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
			$.getJSON('action.php?tpl=json_plugins_list&id='+json_item_datas['jstree']['id'], function(datas) {
				json_item_datas = datas;
			});
		}

$(function () {
    var cache = {}, lastXhr;
    $('#searchtext').autocomplete({
        minLength: 2,
        source: function( request, response ) {
            var term = request.term;
            if ( term in cache ) {
                response( cache[ term ] );
                return;
            }

            lastXhr = $.getJSON( "action.php?tpl=json_actions&action=search&id=0", request, function( data, status, xhr ) {
                cache[ term ] = data;
                if ( xhr === lastXhr ) {
                    response( data );
                }
            });
        },
        select: function(event, ui) { 
            $('#tree').jstree("search", ui.item.label);
        }
    });
    $('#searchtext').keypress(function(event) {
        if ( event.which == 13 ) {
             $('#tree').jstree("search", $('#searchtext').val());
             $('#searchtext').autocomplete("close");
        }
    });
});

function auto_refresh_status() {
	refresh_status();
	window.setTimeout(function () {
	        auto_refresh_status();
    }, 60000);
}

function refresh_status() {
    var lst = '';
    $('li[id^="node_"]').each(function(index, element) {
            if ($('#'+this.id).attr('rel') != 'drive' && $('#'+this.id).attr('rel') != 'folder') {
                lst = lst + this.id.substr(5) + "\n";
            }
        }
    );
    $.ajax({
        async : true,
        type: 'POST',
        url: "index.php?tpl=status",
        dataType : 'json',
        data : { 
            "nodes" : lst
        },
        complete : function (r) {
            if(r.status) {
                var res = jQuery.parseJSON(r.responseText);
                for(i in res['up']) {
                    $('#node_'+res['up'][i]).attr('rel', 'default-green');
                }
                for(i in res['down']) {
                    $('#node_'+res['down'][i]).attr('rel', 'default-red');
                }
                for(i in res['unknown']) {
                    $('#node_'+res['unknown'][i]).attr('rel', 'default-grey');
                }
            }
        }
    });
}

function notify_ko(text) {
	$('[tag="message"] b').css('color', 'red').html(text);
	window.setTimeout(function () {
		$('[tag="message"] b').html('');
	}, 3000);
}

function notify_ok(text) {
	$('[tag="message"] b').css('color', 'green').html(text);
	window.setTimeout(function () {
		$('[tag="message"] b').html('');
	}, 3000);
}

function askfor(optionsarg, func) {
	var options = { label: '', cancellabel: 'Cancel', oklabel: 'Ok', title: 'How mutch ?'};
	$.extend(options, optionsarg);
	$('#modalwindow').jqxWindow({ height: 150, width: 350, title: options['title'], isModal: true, theme: theme }).show();
	$('#modalwindowcontent').html(ich.askfor(options));
	$('#modalwindowcontent input[type="button"]').jqxButton({ theme: theme });
	$('#modalwindowcontent input[tag="cancel"]').click(function () {
		$('#modalwindow').jqxWindow('hide');
	});
	$('#modalwindowcontent div input[tag="ok"]').click(function () {
		$('#modalwindow').jqxWindow('hide');
		func($('#modalwindowcontent input[type="text"]').val());
	});
}

function confirmfor(optionsarg, func) {
	var options = { cancellabel: 'Cancel', oklabel: 'Ok', title: 'How mutch ?'};
	$.extend(options, optionsarg);
	$('#modalwindow').jqxWindow({ height: 70, width: 350, title: options['title'], isModal: true, theme: theme }).show();
	$('#modalwindowcontent').html(ich.confirmfor(options));
	$('#modalwindowcontent input[type="button"]').jqxButton({ theme: theme });
	$('#modalwindowcontent input[tag="cancel"]').click(function () {
		$('#modalwindow').jqxWindow('hide');
	});
	$('#modalwindowcontent div input[tag="ok"]').click(function () {
		$('#modalwindow').jqxWindow('hide');
		func();
	});
}

