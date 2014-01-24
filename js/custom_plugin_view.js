/**
 * Custom plugin view functions for Perfwatcher
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

function custom_plugin_view_generic_graph(graphinfo) {
    if(graphinfo['show_html_legend']) {
        $('<h1>Collectd source '+graphinfo['cdsrc']+' </h1>').appendTo('div[pwtabid="'+graphinfo['pwtabid']+'"] div div[class="graphcontainer"]');
    }
    $('<img class="graph" id="graph_'+graphinfo['pwtabid']+'_'+graphinfo['graphid']+'" zone="tab"/><br/>').appendTo('div[pwtabid="'+graphinfo['pwtabid']+'"] div div[class="graphcontainer"]');
    $('#graph_'+graphinfo['pwtabid']+'_'+graphinfo['graphid']).pwgraph({
        cdsrc: graphinfo['cdsrc'],
        host: graphinfo['host'],
        plugin: graphinfo['plugin'],
        plugin_instance: graphinfo['plugin_instance'],
        type: graphinfo['type'],
        type_instance: graphinfo['type_instance']
    }).pwgraph('display');
}

function custom_plugin_view_generic(pwtabid, plugin, options) {
    var selector0_legend = 'Select Collectd source(s)';
    var selector1_legend = '';
    var selector2_legend = '';
    var selector3_legend = '';
    var selector0_select = 'select[pwtabid="'+pwtabid+'"][part="cdsrc"]';
    var selector1_select = 'select[pwtabid="'+pwtabid+'"][part="plugin_instance"]';
    var selector2_select = 'select[pwtabid="'+pwtabid+'"][part="type"]';
    var selector3_select = 'select[pwtabid="'+pwtabid+'"][part="type_instance"]';
    var cdsrc_list = new Array();
    var pi_list = new Array();
    var t_list = new Array();
    var ti_list = new Array();
    var selector0_show = 0;
    var selector1_show = 0;
    var selector2_show = 0;
    var selector3_show = 0;
    var show_html_legend_per_graph = 0;
    var graph_function = custom_plugin_view_generic_graph;
    var switch_plugin_type_for_collectdv4_check = 0;
    var switch_plugin_type_for_collectdv4_needed = false;
/*
 * if(!switch_plugin_type_for_collectdv4_needed) {
 *   normal mode
 * } else {
 *   move ti to pi.
 *   So plugin instance is '_' and selector 1 contains ti list instead of pi list.
 * }
 */

    if(options['switch_plugin_type_for_collectdv4']) { switch_plugin_type_for_collectdv4_check = options['switch_plugin_type_for_collectdv4']; }
    if(options['selector0_legend']) { selector0_legend = options['selector0_legend']; }
    if(options['selector1_legend']) { selector1_legend = options['selector1_legend']; }
    if(options['selector2_legend']) { selector2_legend = options['selector2_legend']; }
    if(options['selector3_legend']) { selector3_legend = options['selector3_legend']; }

    if(options['graph_function']) { graph_function = options['graph_function']; }

    if(json_item_datas['aggregators']) {
        $.each(json_item_datas['aggregators'], function (cdsrc, aggregator_plugins) {
           $.each(aggregator_plugins, function (current_plugin, current_plugin_instance) {
               if(current_plugin == plugin) {
               $.each(current_plugin_instance, function (plugin_instance_name, plugin_instance) {
                   $.each(plugin_instance, function (type_name, type) {
                       show_html_legend_per_graph = 1;
                       if ($.inArray(cdsrc, cdsrc_list) == -1) { cdsrc_list.push(cdsrc); }
                       if ($.inArray(plugin_instance_name, pi_list) == -1) { pi_list.push(plugin_instance_name); }
                       if ($.inArray(type_name, t_list) == -1) { t_list.push(type_name); }
                       $.each(type, function (type_instance_name, type_instance) {
                           if ($.inArray(type_instance_name, ti_list) == -1) { ti_list.push(type_instance_name); }
                           });
                       });
                   });
               }
               });
           });
    }
    if(json_item_datas['plugins'][plugin]) {
        $.each(json_item_datas['plugins'][plugin], function (plugin_instance_name, plugin_instance) {
           $.each(plugin_instance, function (type_name, type) {
               if ($.inArray(json_item_datas['config']['CdSrc']['source'], cdsrc_list) == -1) { cdsrc_list.push(json_item_datas['config']['CdSrc']['source']); }
               if ($.inArray(plugin_instance_name, pi_list) == -1) { pi_list.push(plugin_instance_name); }
               if ($.inArray(type_name, t_list) == -1) { t_list.push(type_name); }
               $.each(type, function (type_instance_name, type_instance) {
                   if ($.inArray(type_instance_name, ti_list) == -1) { ti_list.push(type_instance_name); }
                   });
               });
           });
    }
    $.each(pi_list, function (i, pi) {
        if (switch_plugin_type_for_collectdv4_check && pi == '_' && pi_list.length == 1) { switch_plugin_type_for_collectdv4_needed = true; }
    });
    $.each(cdsrc_list, function (i, cdsrc) {
        var option = document.createElement('option');
        var value = new Array(cdsrc);
        var ht = cdsrc;
        if(options['selector0_legend_function']) { ht = options['selector0_legend_function'](ht); }
        option.innerHTML = ht;
        $(option).data(value);
        $(option).appendTo(selector0_select);
    });
    if(!switch_plugin_type_for_collectdv4_needed) {
        $.each(pi_list, function (i, pi) {
            var option = document.createElement('option');
            var value = new Array(pi);
            var ht = pi;
            if(options['selector1_legend_function']) { ht = options['selector1_legend_function'](ht); }
            option.innerHTML = ht;
            $(option).data(value);
            $(option).appendTo(selector1_select);
        });
    }
    $.each(t_list, function (i, t) {
        var option = document.createElement('option');
        var value = new Array(t);
        var ht = t;
        if(options['selector2_legend_function']) { ht = options['selector2_legend_function'](ht); }
        option.innerHTML = ht;
        $(option).data(value);
        $(option).appendTo(selector2_select);
    });
    $.each(ti_list, function (i, ti) {
        var option = document.createElement('option');
        var value = new Array(ti);
        var ht = ti;
        if(options['selector3_legend_function']) { ht = options['selector3_legend_function'](ht); }
        option.innerHTML = ht;
        $(option).data(value);
        if(!switch_plugin_type_for_collectdv4_needed) {
            $(option).appendTo(selector3_select);
        } else {
            $(option).appendTo(selector1_select);
        }
    });

    $(selector0_select).multiselect({ noneSelectedText: selector0_legend, selectedList: 0 }).multiselectfilter();
    $(selector1_select).multiselect({ noneSelectedText: selector1_legend, selectedList: 0 }).multiselectfilter();
    $(selector2_select).multiselect({ noneSelectedText: selector2_legend, selectedList: 0 }).multiselectfilter();
    $(selector3_select).multiselect({ noneSelectedText: selector3_legend, selectedList: 0 }).multiselectfilter();

    if(cdsrc_list.length > 1) { selector0_show = 1; } else { $(selector0_select).remove(); }
    if(t_list.length     > 1) { selector2_show = 1; } else { $(selector2_select).remove(); }
    if(!switch_plugin_type_for_collectdv4_needed) {
        if(pi_list.length    > 1) { selector1_show = 1; } else { $(selector1_select).remove(); }
        if(ti_list.length    > 1) { selector3_show = 1; } else { $(selector3_select).remove(); }
    } else {
        if(ti_list.length    > 1) { selector1_show = 1; } else { $(selector1_select).remove(); }
        $(selector3_select).remove();
    }

    var input_selector = 'input[pwtabid='+pwtabid+']';
    $(input_selector).click(function () {
        $('div[pwtabid="'+pwtabid+'"] div div[class="graphcontainer"]').html('');

        var cdsrc_list_selected = new Array();
        var pi_list_selected = new Array();
        var t_list_selected = new Array();
        var ti_list_selected = new Array();

        if(selector0_show) {
            $(selector0_select+' option:selected').each(function() { cdsrc_list_selected.push($(this).data()[0]); });
        } else {
            cdsrc_list_selected = cdsrc_list;
        }
        if(selector1_legend != '' && selector1_show) {
            $(selector1_select+' option:selected').each(function() { pi_list_selected.push($(this).data()[0]); });
        } else {
            if(!switch_plugin_type_for_collectdv4_needed) {
                pi_list_selected = pi_list;
            } else {
                pi_list_selected = ti_list;
            }
        }
        if(selector2_legend != '' && selector2_show) {
           $(selector2_select+' option:selected').each(function() { t_list_selected.push($(this).data()[0]); });
        } else {
           t_list_selected = t_list;
        }
        if(!switch_plugin_type_for_collectdv4_needed) {
            if(selector3_legend != '' && selector3_show) {
               $(selector3_select+' option:selected').each(function() { ti_list_selected.push($(this).data()[0]); });
            } else {
               ti_list_selected = ti_list;
            }
        }
        if(pi_list_selected.length == 0) { pi_list_selected.push(''); }
        if(t_list_selected.length == 0) { t_list_selected.push(''); }
        if(ti_list_selected.length == 0) { ti_list_selected.push(''); }

        $(cdsrc_list_selected).each(function(i, cdsrc) {
            $(pi_list_selected).each(function(i, pi) {
                $(t_list_selected).each(function(i, t) {
                    $(ti_list_selected).each(function(i, ti) {
                        graphid++;
                        var graphinfo = {
                            show_html_legend: show_html_legend_per_graph,
                            pwtabid: pwtabid,
                            graphid: graphid,
                            cdsrc: cdsrc,
                            host: json_item_datas['host'],
                            plugin: plugin,
                            plugin_instance: (!switch_plugin_type_for_collectdv4_needed)?pi:'',
                            type: t,
                            type_instance: (!switch_plugin_type_for_collectdv4_needed)?ti:pi
                        };
                        graph_function(graphinfo);
                    });
                });
            });
        });
    }).jqxButton({ theme: theme });
}

function custom_plugin_view_generic_one_selector(pwtabid, plugin, options) {
    var selector0_legend = 'Select Collectd source(s)';
    var selector1_legend = '';
    var selector0_select = 'select[pwtabid="'+pwtabid+'"][part="cdsrc"]';
    var selector1_select = 'select[pwtabid="'+pwtabid+'"][part="plugin_filter"]';
    var cdsrc_list = new Array();
    var selector0_show = 0;
    var selector1_show = 0;
    var show_html_legend_per_graph = 0;
    var graph_function = custom_plugin_view_generic_graph;

    if(options['selector0_legend']) { selector0_legend = options['selector0_legend']; }
    if(options['selector1_legend']) { selector1_legend = options['selector1_legend']; }

    if(options['graph_function']) { graph_function = options['graph_function']; }

    if(json_item_datas['aggregators']) {
        $.each(json_item_datas['aggregators'], function (cdsrc, aggregator_plugins) {
            $.each(aggregator_plugins, function (current_plugin, current_plugin_instance) {
                if(current_plugin == plugin) {
                    $.each(current_plugin_instance, function (plugin_instance_name, plugin_instance) {
                        $.each(plugin_instance, function (type_name, type) {
                            $.each(type, function (type_instance_name, type_instance) {
                                if ($.inArray(cdsrc, cdsrc_list) == -1) { cdsrc_list.push(cdsrc); }
                                var option = document.createElement('option');
                                var value = new Array(cdsrc, plugin, plugin_instance_name, type_name, type_instance_name);
                                var ht = plugin_instance_name+'-'+type_instance_name;
                                show_html_legend_per_graph = 1;
                                if(options['selector1_legend_function']) { ht = options['selector1_legend_function'](value); }
                                option.innerHTML = ht;
                                $(option).data(value);
                                $(option).appendTo(selector1_select);
                            });
                        });
                    });
                }
            });
        });
    }
    if(json_item_datas['plugins'][plugin]) {
        cdsrc_list.push(json_item_datas['config']['CdSrc']['source']);
        $.each(json_item_datas['plugins'][plugin], function (plugin_instance_name, plugin_instance) {
            $.each(plugin_instance, function (type_name, type) {
                $.each(type, function (type_instance_name, type_instance) {
                    var option = document.createElement('option');
                    var value = new Array(json_item_datas['config']['CdSrc']['source'], plugin, plugin_instance_name, type_name, type_instance_name);
                    var ht = plugin_instance_name+'-'+type_instance_name;
                    if(options['selector1_legend_function']) { ht = options['selector1_legend_function'](value); }
                    option.innerHTML = ht;
                    $(option).data(value);
                    $(option).appendTo(selector1_select);
                });
            });
        });
    }
    $.each(cdsrc_list, function (i, cdsrc) {
        var option = document.createElement('option');
        var value = new Array(cdsrc);
        var ht = cdsrc;
        if(options['selector0_legend_function']) { ht = options['selector0_legend_function'](ht); }
        option.innerHTML = ht;
        $(option).data(value);
        $(option).appendTo(selector0_select);
        });

    $(selector0_select).multiselect({ noneSelectedText: selector0_legend, selectedList: 0 }).multiselectfilter();
    $(selector1_select).multiselect({ noneSelectedText: selector1_legend, selectedList: 0 }).multiselectfilter();

    if(cdsrc_list.length > 1) { selector0_show = 1; } else { $(selector0_select).remove(); }
    selector1_show = 1;

    var input_selector = 'input[pwtabid='+pwtabid+']';
    $(input_selector).click(function () {
        $('div[pwtabid="'+pwtabid+'"] div div[class="graphcontainer"]').html('');

        var cdsrc_list_selected = new Array();
        if(selector0_show) {
            $(selector0_select+' option:selected').each(function() { cdsrc_list_selected.push($(this).data()[0]); });
        } else {
           cdsrc_list_selected = cdsrc_list;
        }

        $(cdsrc_list_selected).each(function(i, cdsrc) {
            $(selector1_select+' option:selected').each(function() {
                graphid++;
                var graphinfo = {
                    show_html_legend: show_html_legend_per_graph,
                    pwtabid: pwtabid,
                    graphid: graphid,
                    cdsrc: $(this).data()[0],
                    host: json_item_datas['host'],
                    plugin: $(this).data()[1],
                    plugin_instance: $(this).data()[2],
                    type: $(this).data()[3],
                    type_instance: $(this).data()[4]
                };
                graph_function(graphinfo);
            });
        });
    }).jqxButton({ theme: theme });
}


function cpu_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/cpu_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('cpu_plugin_view', r.responseText);
				ich.cpu_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function df_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/df_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('df_plugin_view', r.responseText);
				ich.df_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function disk_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/disk_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('disk_plugin_view', r.responseText);
				ich.disk_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function interface_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/interface_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('interface_plugin_view', r.responseText);
				ich.interface_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function disk_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/disk_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('disk_plugin_view', r.responseText);
				ich.disk_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function processes_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/processes_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('processes_plugin_view', r.responseText);
				ich.processes_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function protocols_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/protocols_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('protocols_plugin_view', r.responseText);
				ich.protocols_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function tcpconns_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/tcpconns_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('tcpconns_plugin_view', r.responseText);
				ich.tcpconns_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

function vmem_plugin_view(pwtabid, plugin) {
	$.ajax({
	    async : true,
	    type: 'GET',
	    url: 'html/vmem_plugin_view.html',
	    complete : function (r) {
	        if(r.status) {
				ich.addTemplate('vmem_plugin_view', r.responseText);
				ich.vmem_plugin_view({ pwtabid: pwtabid, plugin: plugin }).appendTo('div[pwtabid="'+pwtabid+'"]');
	        }
	    }
	});
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
