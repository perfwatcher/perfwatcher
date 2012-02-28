/**
 * Common functions
 *
 * PHP version 5
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
$(function () {
	// Settings up the tree - using $(selector).jstree(options);
	// All those configuration options are documented in the _docs folder
	$('#tree')
		.jstree({ 
			// the list of plugins to include
			"plugins" : [ "themes", "json_data", "ui", "crrm", "cookies", "dnd", "search", "types", "hotkeys", "contextmenu" ],
			// Plugin configuration

            "themes" : {
                "url" : "css/themes/default/style.css",
                "theme" : "default",
                "dots" : true,
                "icons" : true
            },

			// I usually configure the plugin that handles the data first - in this case JSON as it is most common
			"json_data" : { 
				// I chose an ajax enabled tree - again - as this is most common, and maybe a bit more complex
				// All the options are the same as jQuery's except for `data` which CAN (not should) be a function
				"ajax" : {
					// the URL to fetch the data
					"url" : "index.php?tpl=json_tree",
					// this function is executed in the instance's scope (this refers to the tree instance)
					// the parameter is the node being loaded (may be -1, 0, or undefined when loading the root nodes)
					"data" : function (n) { 
						// the result is fed to the AJAX request `data` option
						return { 
							"operation" : "get_children", 
							"id" : n.attr ? n.attr("id").replace("node_","") : 1 
						}; 
					}
				}
			},
			// Configuring the search plugin
			"search" : {
				// As this has been a common question - async search
				// Same as above - the `ajax` config option is actually jQuery's object (only `data` can be a function)
                "case_insensitive" : true,
				"ajax" : {
					"url" : "index.php?tpl=json_tree",
					"data" : function (str) {
						return { 
							"operation" : "search", 
							"search_str" : str 
						}; 
					}
				}
			},
			// Using types - most of the time this is an overkill
			// Still meny people use them - here is how
			"types" : {
				// I set both options to -2, as I do not need depth and children count checking
				// Those two checks may slow jstree a lot, so use only when needed
				"max_depth" : -2,
				"max_children" : -2,
				// I want only `drive` nodes to be root nodes 
				// This will prevent moving or creating any other type as a root node
				"valid_children" : [ "drive", "folder" ],
				"types" : {
					// The default type
					"default" : {
						// I want this type to have no children (so only leaf nodes)
						// In my case - those are files
						"valid_children" : "none",
						// If we specify an icon for the default type it WILL OVERRIDE the theme icons
						"icon" : {
							"image" : "img/file-grey.png"
						}
					},
					"default-red" : {
						// I want this type to have no children (so only leaf nodes)
						// In my case - those are files
						"valid_children" : "none",
						// If we specify an icon for the default type it WILL OVERRIDE the theme icons
						"icon" : {
							"image" : "img/file-red.png"
						}
					},
					"default-green" : {
						// I want this type to have no children (so only leaf nodes)
						// In my case - those are files
						"valid_children" : "none",
						// If we specify an icon for the default type it WILL OVERRIDE the theme icons
						"icon" : {
							"image" : "img/file-green.png"
						}
					},
					// The `folder` type
					"folder" : {
						// can have files and other folders inside of it, but NOT `drive` nodes
						"valid_children" : [ "default", "default-red", "default-grey", "default-green", "folder" ],
						"icon" : {
							"image" : "img/folder.png"
						}
					},
					// The `drive` nodes 
					"drive" : {
						// can have files and folders inside, but NOT other `drive` nodes
						"valid_children" : [ "default", "default-red", "default-grey", "default-green", "folder" ],
						"icon" : {
							"image" : "img/folder.png"
						},
						// those options prevent the functions with the same name to be used on the `drive` type nodes
						// internally the `before` event is used
						"start_drag" : false,
						"move_node" : false,
						"delete_node" : false,
						"remove" : false
					}
				}
			},
			// For UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

			// the UI plugin - it handles selecting/deselecting/hovering nodes
			"ui" : {
				// this makes the node with ID node_4 selected onload
				"initially_select" : [ ]
			},
			"hotkeys" : {
				"del" : function () { confirm('Are you sure ?') && $('#tree').jstree("remove"); }
			},
			// the core plugin - not many options here
			"core" : { 
				// just open those two nodes up
				// as this is an AJAX enabled tree, both will be downloaded from the server
				"initially_open" : [ ],
                "strings" : { loading : "Loading ...", new_node : "Untiteled"}
			},
            "contextmenu" : {
                "select_node" : true,
                "items" : {
                    "create" : {
                        "separator_before"	: false,
                        "separator_after"	: true,
                        "label"				: "Add",
                        "action"			: false,
                        "submenu"           : {
                            "createserver" : {
                                "separator_before"	: false,
                                "separator_after"	: true,
                                "label"				: "Server",
                                "action"			: function (obj) { this.create(obj); }
                            },
                            "createfolder" : {
                                "separator_before"	: false,
                                "separator_after"	: true,
                                "label"				: "Folder",
				                //"action"            : function (obj) { $('#tree').jstree("create", obj, "last", { "attr" : { "rel" : "folder" } }); }
                                "action"			: function (obj) { this.create(obj, "last", { "attr" : { "rel" : "folder" } }); }
                            }
                        }
                    },
                    "setting" : {
                        "separator_before"	: true,
                        "separator_after"	: false,
                        "label"				: "Configure",
                        "action"            : function () { $('#configure').click(); }
                    }
                }
            }
		})
		.bind("create.jstree", function (e, data) {
			$.post(
				"admin/index.php?tpl=json_tree", 
				{ 
					"operation" : "create_node", 
					"id" : data.rslt.parent.attr("id").replace("node_",""), 
					"position" : data.rslt.position,
					"title" : data.rslt.name,
					"type" : data.rslt.obj.attr("rel")
				}, 
				function (r) {
					if(r.status) {
						$(data.rslt.obj).attr("id", "node_" + r.id);
					}
					else {
						$.jstree.rollback(data.rlbk);
					}
				}
			);
		})
		.bind("remove.jstree", function (e, data) {
			data.rslt.obj.each(function () {
				$.ajax({
					async : false,
					type: 'POST',
					url: "admin/index.php?tpl=json_tree",
					data : { 
						"operation" : "remove_node", 
						"id" : this.id.replace("node_","")
					}, 
					success : function (r) {
						if(!r.status) {
							data.inst.refresh();
						}
					}
				});
			});
		})
		.bind("rename.jstree", function (e, data) {
			$.post(
				"admin/index.php?tpl=json_tree", 
				{ 
					"operation" : "rename_node", 
					"id" : data.rslt.obj.attr("id").replace("node_",""),
					"title" : data.rslt.new_name
				}, 
				function (r) {
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
				}
			);
		})
		.bind("move_node.jstree", function (e, data) {
			data.rslt.o.each(function (i) {
				$.ajax({
					async : false,
					type: 'POST',
					url: "admin/index.php?tpl=json_tree",
					data : { 
						"operation" : "move_node", 
						"id" : $(this).attr("id").replace("node_",""), 
						"ref" : data.rslt.np.attr("id").replace("node_",""), 
						"position" : data.rslt.cp + i,
						"title" : data.rslt.name,
						"copy" : data.rslt.cy ? 1 : 0
					},
					success : function (r) {
						if(!r.status) {
							$.jstree.rollback(data.rlbk);
						}
						else {
							$(data.rslt.oc).attr("id", "node_" + r.id);
							if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
								data.inst.refresh(data.inst._get_parent(data.rslt.oc));
							}
						}
						//$("#analyze").click();
					}
				});
			});
		})
		.bind("select_node.jstree", function (e, data) {
			$.ajax({
				async : false,
				type: 'POST',
				url: "index.php?tpl=rrdlist",
				data : { 
					"id" : data.rslt.obj.attr("id").replace("node_","") 
				},
				complete : function (r) {
					if(!r.status) {
						('#content').html('Error, can\'t retrieve data from server !');
					}
					else {
                        $('#content').html(r.responseText);
					}
				}
			})
		});
});

$(function () { 
	$('#refresh_tree').button().click(function () {
        $('#tree').jstree("refresh");
    });
    var cache = {}, lastXhr;
    $('#searchtext').autocomplete({
        minLength: 2,
        source: function( request, response ) {
            var term = request.term;
            if ( term in cache ) {
                response( cache[ term ] );
                return;
            }

            lastXhr = $.getJSON( "index.php?tpl=searchserver", request, function( data, status, xhr ) {
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
//setTimeout("refresh_status()", 10000);

