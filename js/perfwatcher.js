
/**
 * Common JS
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 **/ 
var json_item_datas = new Array();
var current_graph = null;
var pwgraph_hover_enabled = true;
var current_tab = null;
graphid = 0;
var treecollapsed = false;
var contextMenu;
var clipboard = new Array();
function positionsubmenu(position, elements) {
		var options = {
			of: elements.target.element   
		};
		if(elements.element.element.parent().parent().parent().attr('id') === "headerLeft") {
				options.my = "center top";
				options.at = "center bottom";
		} else {
				options.my = "left top";
				options.at = "right bottom";
		}
		elements.element.element.position(options);
}
function add_to_clipboard(event, ui) {
    var txt = ui.helper.text();
	clipboard.push(txt);
	$("#clip_content").html(clipboard.length+" elements");
}

$(document).ready(function() {
	$.ajax({
		async : false, type: 'GET', url: 'action.php',
		data : { 'action': 'get_js', 'tpl': 'json_actions', '_': (new Date()).getTime(), 'id': 0 },
		complete : function (r) {
			if(r.status) {
				var jsfiles = jQuery.parseJSON(r.responseText);
				for (i in jsfiles) {
					$(document).find('head').append('<script type="text/javascript" src="'+jsfiles[i]+'"></script>');
				}
			}
		}
	});
	$('#headerLeft').html(ich.mainMenutpl({}));
	$('#headerCenter').html(ich.headerCentertpl({}));
	$('#headerRight').html(ich.headerRighttpl({}));
	$(ich.contextmenutpl({})).appendTo('body');
	theme = getTheme();
	$('#headerLeft > ul').menu({ position: { using: positionsubmenu }});
	$('#headerLeft > ul > li > a > span.ui-icon-carat-1-e').removeClass('ui-icon');
	$('#headerLeft').show();
	$('#mainSplitter').jqxSplitter({
		panels: [{ size: '300px' }],
		theme: theme,
		height: $(document).height() - $('#mainMenu').height() - 1,
		splitBarSize: '2px'
	});
	window.setTimeout(function () {
		auto_refresh_status();
	}, 10000);

	$('#clip').droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			tolerance: "pointer",
			drop: function(event, ui) { add_to_clipboard(event, ui); }
		});

	$('#timebutton').html(ich.timebuttontpl({}));
	$('#timebutton').hide();
	$('#timespan').hide();
	$('#timespan').dblclick(function() {
		$(current_graph).pwgraph('applytimespan').pwgraph('display');
		$(this).hide();
	});
	$('#timebutton div div').click(function () {
		var method = $(this).attr('class');
		$(current_graph).pwgraph(method).pwgraph('display');
	});
	$('#datetime').html((new Date).toString()).hide();
	
	$('#mainSplitter').bind('collapsed', function (event) {
		treecollapsed = true;
	});
	
	$('#clip_content').click(function() {
					alert("NOT FINISHED YET / TODO\n"+JSON.stringify(clipboard));
	});
	$('a[pwmenuid^="menu_"]').click(function () {
		//console.log($(this).attr("id"));
		switch($(this).attr("pwmenuid")) {
			case 'menu_view_new':
				askfor({ title: 'New view name' }, function(title) {
					$.ajax({
						async : false, type: "POST", url: "action.php?tpl=json_actions",
						data : { "action" : "new_view", "view_title" : title },
						complete : function (r) {
							if(!r.status) {
								notify_ko('Error, can\'t retrieve data from server !');
							} else {
								ids = jQuery.parseJSON(r.responseText);
								if((ids['id'] != -1) && (ids['view_id'] != -1)) {
									view_id = ids['view_id'];
									location.hash = "id_"+view_id;
									$('#tree').jstree("refresh");
									notify_ok("New view is created");
								} else {
									notify_ko("New view creation failed");
								}
							}
						}
					});
				});
			break;
			case 'menu_view_open':
				select_view(function() {
						location.hash = "id_"+view_id;
						$('#tree').jstree("refresh");
				});
			break;
			case 'menu_view_delete':
				confirmfor({title: 'Do you really want to delete this view ?'}, function() {
					$.ajax({
						async : false, type: "POST", url: "action.php?tpl=json_actions",
						data : { "action" : "delete_view", "view_id" : view_id },
						complete : function (r) {
							if(!r.status) {
								notify_ko('Error, can\'t retrieve data from server !');
							} else {
								result = jQuery.parseJSON(r.responseText);
								if(result['view_id'] > 0) {
									view_id = result['view_id'];
									location.hash = "id_"+view_id;
									$('#tree').jstree("refresh");
									notify_ok("View deleted");
								} else {
									notify_ko("View could not be deleted");
								}
							}
						}
					});
				});
			break;
			case 'menu_new_tab':
				askfornewtab({ }, function(title, lifetime) {
					$.ajax({
						async : false, type: "POST", url: "action.php?tpl=json_actions",
						data : { "action" : "add_tab", "view_id" : view_id, "id" : json_item_datas['jstree']['id'], "tab_title" : title == '' ? 'Custom view' : title, "lifetime": lifetime },
						complete : function (r) {
							if(!r.status) {
								notify_ko('Error, can\'t retrieve data from server !');
							} else {
								select_node(json_item_datas['jstree']['id']);
								notify_ok("Tab added");
							}
						}
					});
				});
			break;
			case 'menu_rename_node':
				$('#tree').jstree("rename");
			break;
			case 'menu_rename_tab':
			break;
			case 'menu_delete_node':
				confirmfor({title: 'Do you really want to delete this server / container ?'}, function() {
					$('#tree').jstree("remove");
				});
			break;
			case 'menu_delete_tab':
				confirmfor({title: 'Do you really want to delete this tab ?'}, function() {
					if($('div[tabid="'+current_tab+'"]').attr('custom_tab_id')) {
						$.ajax({
							async : false, type: "POST", url: "action.php?tpl=json_actions",
							data : { "action" : "del_tab", "selection_id" : $('div[tabid="'+current_tab+'"]').attr('custom_tab_id') },
							complete : function (r) {
								if(!r.status) {
									notify_ko('Error, can\'t retrieve data from server !');
								} else {
									select_node(json_item_datas['jstree']['id']);
									notify_ok("Tab removed");
								}
							}
						});
					}
				});
			break;
			case 'menu_copy':
				$('#tree').jstree("copy");
			break;
			case 'menu_paste':
				$('#tree').jstree("paste");
			break;
			case 'menu_cut':
				$('#tree').jstree("cut");
			break;
			case 'menu_new_server':
				$('#tree').jstree("create", null, "last", { "attr" : { "rel" : "default", "pwtype" : "server" } });
			break;
			case 'menu_new_container':
				$('#tree').jstree("create", null, "last", { "attr" : { "rel" : "folder", "pwtype" : "container" } });
			break;
			case 'menu_display_toggle_tree':
				if (treecollapsed) {
					$('#mainSplitter').jqxSplitter('expandAt', 0);
					treecollapsed = false;
				} else {
					$('#mainSplitter').jqxSplitter('collapseAt', 0);
					treecollapsed = true;
				}
			break;
			case 'menu_display_in_new_window':
				alert('not implemented yet');
			break;
			case 'menu_refresh_tree':
				$('#tree').jstree("refresh");
			break;
			case 'menu_refresh_node':
				select_node(json_item_datas['jstree']['id']);
			break;
			case 'menu_refresh_status':
				refresh_status();
			break;
			case 'menu_about_box':
				perfwatcher_about_box();
			break;
			default:
				console.log('Undefined stuff : '+ $(this).attr("id"));
			break;
		}
	});

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

				lastXhr = $.getJSON( "action.php?tpl=json_actions&action=search&id=0&view_id="+view_id, request, function( data, status, xhr ) {
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

});

