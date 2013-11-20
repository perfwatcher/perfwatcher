/**
 * Perfwatcher jsTree config
 *
 * Javascript
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

var view_id = 1;

$(function () {
	// Settings up the tree - using $(selector).jstree(options);
	// All those configuration options are documented at http://www.jstree.com/
	$('#tree').jstree({ 
	    // the list of plugins to include
	    "plugins" : [ "themes", "json_data", "ui", "crrm", "dnd", "search", "types", "contextmenu" ],
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
			    "url" : "action.php?tpl=json_tree",
			    // this function is executed in the instance's scope (this refers to the tree instance)
			    // the parameter is the node being loaded (may be -1, 0, or undefined when loading the root nodes)
			    "data" : function (n) { 
				// the result is fed to the AJAX request `data` option
						if (location.hash) { 
								var h = location.hash.substr(1).split('_');
								if(h[0] == 'id') {
										h.shift();
										view_id = h[0];
								}
						}
					return { 
						"operation" : "get_children", 
						"view_id" : view_id,
						"id" : n.attr ? n.attr("id").replace("node_","") : 1
					}; 
			    },
			    "error" : function (data) {
			    	$('body').html(data.responseText);
			    }
			}
	    },
	    // Configuring the search plugin
	    "search" : {
			// As this has been a common question - async search
			// Same as above - the `ajax` config option is actually jQuery's object (only `data` can be a function)
			"case_insensitive" : true,
			"ajax" : {
		    	"url" : "action.php?tpl=json_tree",
		    	"data" : function (str) {
					return { 
			    		"operation" : "search", 
						"view_id" : view_id,
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
		"valid_children" : [ "folder" ],
		"types" : {
		    // The default type
		    "default" : {
				// I want this type to have no children (so only leaf nodes)
				// In my case - those are files
				"valid_children" : "none",
				// If we specify an icon for the default type it WILL OVERRIDE the theme icons
				"icon" : { "image" : "img/file-grey.png" }
		    },
		    "default-red" : { "valid_children" : "none", "icon" : { "image" : "img/file-red.png" } },
		    "default-green" : { "valid_children" : "none", "icon" : { "image" : "img/file-green.png" } },
		    // The `folder` type
		    "folder" : {
				// can have files and other folders inside of it, but NOT `drive` nodes
				"valid_children" : [ "default", "default-red", "default-grey", "default-green", "folder" ],
				"icon" : { "image" : "img/folder.png" }
		    },
		    // The `drive` nodes 
		    "drive" : {
				// can have files and folders inside, but NOT other `drive` nodes
				"valid_children" : [ "default", "default-red", "default-grey", "default-green", "folder" ],
				"icon" : { "image" : "img/folder.png" },
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
	    // the core plugin - not many options here
	    "core" : { 
		// just open those two nodes up
		// as this is an AJAX enabled tree, both will be downloaded from the server
		"initially_open" : [ ],
		"strings" : { loading : "Loading ...", new_node : "Untiteled"}
	    },
	    "contextmenu" : {
		"select_node" : false,
		"items" : {
		    "create" : {
			"separator_before"	: false,
			"separator_after"	: true,
			"label"			: "Add",
			"action"		: false,
			"submenu"           	: {
			    "createserver" 	: {
				"separator_before"	: false,
				"separator_after"	: true,
				"label"			: "Server",
				"action"		: function (obj) { this.create(obj); }
			    },
			    "createfolder" 	: {
				"separator_before"	: false,
				"separator_after"	: true,
				"label"			: "Folder",
				"action"		: function (obj) { this.create(obj, "last", { "attr" : { "rel" : "folder" } }); }
			    }
			}
		    }
		}
	    }
	})
	.bind("create.jstree", function (e, data) {
	    $.post(
		"action.php?tpl=json_tree", 
		{ 
		    "operation" : "create_node", 
			"view_id" : view_id,
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
	    }).bind("remove.jstree", function (e, data) {
		data.rslt.obj.each(function () {
		    $.ajax({
			async : false,
			type: 'POST',
			url: "action.php?tpl=json_tree",
			data : { 
			    "operation" : "remove_node", 
				"view_id" : view_id,
			    "id" : this.id.replace("node_","")
			}, 
			success : function (r) {
			    if(!r.status) {
				data.inst.refresh();
			    }
			}
		    });
		});
	    }).bind("rename.jstree", function (e, data) {
		$.post(
		    "action.php?tpl=json_tree", 
		    { 
			"operation" : "rename_node", 
			"view_id" : view_id,
			"id" : data.rslt.obj.attr("id").replace("node_",""),
			"title" : data.rslt.new_name
		    }, 
		    function (r) {
			if(!r.status) {
			    $.jstree.rollback(data.rlbk);
			}
		    }
	       );
	}).bind("move_node.jstree", function (e, data) {
	    data.rslt.o.each(function (i) {
		$.ajax({
		    async : false,
		    type: 'POST',
		    url: "action.php?tpl=json_tree",
		    data : { 
			"operation" : "move_node", 
			"view_id" : view_id,
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
		    }
		});
	    });
	}).bind("select_node.jstree", function (e, data) {
	    select_node(data.rslt.obj.attr("id").replace("node_",""));
		//location.hash = data.rslt.obj.attr("id").replace("node_","");
		var path = $("#tree").jstree("get_path", data.rslt.obj, true);
		var hash = '';
		for (n in path) {
			hash = hash + '_' + path[n].replace('node_', '');
		}
		location.hash = 'id_'+view_id+'_'+hash.substr(1);
	}).bind("loaded.jstree", function (event, data) {
		if (!location.hash) { return; }
		var nodes = location.hash.substr(1).split('_');
		if(nodes[0] == 'id') {
			nodes.shift();
			view_id = nodes[0];
			nodes.shift();
			recurse_open_node(nodes);
		} else if(nodes[0] == 'host') {
			var fullhost = location.hash.substr(6);
			select_node_by_name(fullhost);
			$('#mainSplitter').jqxSplitter('collapseAt', 0);
			treecollapsed = true;
		}
    });

	function recurse_open_node (nodes) {
		var node = nodes.shift();
		if (nodes.length == 0) { 
			$('#node_'+node+' a').click();
		} else {
			$('#tree').jstree("open_node", $('#node_'+node), function (event, data) { recurse_open_node (nodes); }, true);
		}
	}
});
