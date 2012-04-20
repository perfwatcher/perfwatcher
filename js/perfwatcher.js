
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
var current_tab = null;
graphid = 0;
var now = Math.round((new Date()).getTime() / 1000);
var treecollapsed = false;
$(document).ready(function() {
	$('#mainMenu').html(ich.mainMenutpl({}));
	theme = getTheme();
	$('#mainMenu').jqxMenu({ theme: theme });
	$('#mainSplitter').jqxSplitter({
		panels: [{ size: '300px' }],
		theme: theme,
		height: $(document).height() - $('#mainMenu').height() - 1,
		splitBarSize: '2px'
	});
	notify_ok('Welcome');
	window.setTimeout(function () {
		auto_refresh_status();
	}, 5000);
		


	$('#timebutton').html(ich.timebuttontpl({}));
	$('#timebutton').hide();
	$('#timebutton div div').click(function () {
		var method = $(this).attr('class');
		$(current_graph).pwgraph(method).pwgraph('display');
	});
	$('#datetime').html((new Date).toLocaleString()).hide();
	
	$('#mainSplitter').bind('collapsed', function (event) {
		treecollapsed = true;
	});
	
	$('a[id^="menu_"]').click(function () {
		switch($(this).attr("id")) {
			case 'menu_new_tab':
				askfor({title: 'Enter a name for this new tab', oklabel: 'Create'}, function(title) {
					$.ajax({
						async : false, type: "POST", url: "action.php?tpl=json_actions",
						data : { "action" : "add_tab", "id" : json_item_datas['jstree']['id'], "tab_title" : title == '' ? 'Custom view' : title },
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
							data : { "action" : "del_tab", "id" : json_item_datas['jstree']['id'], "tab_id" : $('div[tabid="'+current_tab+'"]').attr('custom_tab_id') },
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
				$('#tree').jstree("create", null, "last", { "attr" : { "rel" : "default" } });
			break;
			case 'menu_new_container':
				$('#tree').jstree("create", null, "last", { "attr" : { "rel" : "folder" } });
			break;
			case 'menu_new_aggregator':
				askfor({title: 'Coucou ?', cancellabel: 'Nan'}, function(text) {
					console.log(text);
				});
			break;
			case 'menu_view_toogle_tree':
				if (treecollapsed) {
					$('#mainSplitter').jqxSplitter('expandAt', 0);
				} else {
					$('#mainSplitter').jqxSplitter('collapseAt', 0);
					treecollapsed = true;
				}
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
		}
	});
});

