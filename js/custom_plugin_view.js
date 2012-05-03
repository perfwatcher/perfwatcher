

function cpu_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/cpu_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('cpu_plugin_view', r.responseText);
				ich.cpu_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function df_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/df_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('df_plugin_view', r.responseText);
				ich.df_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function disk_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/disk_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('disk_plugin_view', r.responseText);
				ich.disk_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function interface_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/interface_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('interface_plugin_view', r.responseText);
				ich.interface_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function disk_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/disk_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('disk_plugin_view', r.responseText);
				ich.disk_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function protocols_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/protocols_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('protocols_plugin_view', r.responseText);
				ich.protocols_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function tcpconns_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/tcpconns_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('tcpconns_plugin_view', r.responseText);
				ich.tcpconns_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}

function vmem_plugin_view(tabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/vmem_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('vmem_plugin_view', r.responseText);
				ich.vmem_plugin_view({ tabid: tabid, plugin: plugin }).appendTo('div[tabid="'+tabid+'"]');
	        }
	    }
	});
}
