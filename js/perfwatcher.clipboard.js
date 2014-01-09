/**
 * Clipboard functions for Perfwatcher
 *
 * Copyright (c) 2014 Yves METTIER
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
 * @author    Yves Mettier <ymettier at free fr>
 * @copyright 2014 Yves Mettier
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/

function clipboard_update_title() {
	$("#clip_content > span ").text("Clip : "+clipboard.length);
}
function clipboard_append(txt) {
	clipboard.push(txt);
	clipboard_update_title();
}

function clipboard_empty() {
	clipboard = [];
	clipboard_update_title();
}

function clipboard_prepare_dialog() {
    var grouped_types = get_grouped_types();
    $('#modalcliplist').append("<ul></ul>");
    $.each(clipboard, function(k,v) {
        var img = pwmarkdown_filter(v);
        $('#modalcliplist ul').append(
            "<li class='ui-state-default'>"
            +"<div class='clipboard_item'>"
            +img
            +"<span class='clipboard_string'>"+v+"</span>"
            +"<button class='rm_from_clipboard'>Remove from clipboard</button>"
            +"</div>"
            +"</li>");
    });
    $('#modalcliplist span[class="rrdgraph_to_render"]').each(function(idx) {
        var item_current = $(this);
        var code=decodeURIComponent($(this).attr('rrdgraph'));
        var graph_vars = [];
        $(this).removeAttr('rrdgraph');
        $(this).text(code);
        try {
            graph_vars = $.parseJSON(code);
        } catch(e) {
            console.log(e);
            console.log("json was :\n"+code);
        }

        var check_grouped_type = jQuery.inArray(graph_vars['type'], grouped_types);
        var g = {
            "cdsrc": graph_vars['cdsrc'],
            "host": graph_vars['host'],
            "plugin": graph_vars['plugin'],
            "plugin_instance": graph_vars['plugininstance']?graph_vars['plugininstance']:"",
            "type": graph_vars['type'],
            "type_instance": (check_grouped_type == -1)?(graph_vars['typeinstance']?graph_vars['typeinstance']:""):""
            };

        var item_graph = $('<img class="graph" id="graph_'+graphid+'" zone="clip"/>');
        item_graph.insertAfter(item_current);
        item_graph.pwgraph(g).pwgraph('display');
        item_current = item_graph;

        graphid++;
        $(this).hide();
    });
    $('#modalcliplist ul').sortable({ cancel: "img" });

    $('.rm_from_clipboard').click(function () {
        $(this).parent().parent().remove();
        $('#timebutton').hide();
        $('#timespan').hide();
        $('#datetime').hide();
    });
}

function clipboard_refresh_view() {
    var pwrole = $('#clipboard_switch_markdown_btn').attr('pwrole');
    if(pwrole == "markdown") {
        $('#clipboard_switch_markdown_btn').text('Show graphs');
        $('#modalcliplist ul .clipboard_string').show();
        $('#modalcliplist ul .rm_from_clipboard').hide();
        $('#modalcliplist ul img.graph').hide();
        $('#timebutton').hide();
        $('#timespan').hide();
        $('#datetime').hide();
    } else {
        $('#clipboard_switch_markdown_btn').text('Show markdown');
        $('#modalcliplist ul .clipboard_string').hide();
        $('#modalcliplist ul .rm_from_clipboard').show();
        $('#modalcliplist ul img.graph').show();
    }
}

function clipboard_switch_view() {
    var pwrole = $('#clipboard_switch_markdown_btn').attr('pwrole');
    if(pwrole == "graph") {
        $('#clipboard_switch_markdown_btn').attr('pwrole', 'markdown');
    } else {
        $('#clipboard_switch_markdown_btn').attr('pwrole', 'graph');
    }
    clipboard_refresh_view();
}

function clipboard_new_dialog() {
    $('<div id="modalclipcontent"></div>')
        .html('<div>'
            +'<div id="modalclipheader">'
            +'<table><tr>'
            +'<td><button id="clipboard_rollback_btn">Cancel changes</button></td>'
            +'<td><button id="clipboard_switch_markdown_btn" pwrole="graph"></button></td>'
            +'<td><p>This is the contents of your clipboard. You cannot save it. But you can paste it to a selection/tab</p></td>'
            +'</tr></table>'
            +'</div>'
            +'<div id="modalcliplist"></div>'
            )
        .dialog({
            autoOpen: true,
            appendTo: '#clip',
            width: '800px',
            maxHeight: $(window).height() - 50,
            position: {my: 'right top', at: 'bottom left', of: '#clip' },
            title: 'Clipboard contents',
            close: function(event,ui) {
                clipboard = [];
                $('#modalcliplist span.clipboard_string').each(function(i) {
                    clipboard.push($(this).text());
                });
                clipboard_update_title();
                pwgraph_current_zone = "tab";
                $('#modalclipcontent').html("");
                $(this).dialog('destroy').remove();
                $('#timebutton').hide();
                $('#timespan').hide();
                $('#datetime').hide();
            },
            open: function(event, ui) {
                pwgraph_current_zone = "clip";
                clipboard_prepare_dialog();
                clipboard_refresh_view();
            }
        })
        .show();
}

function show_clipboard_contents(tabid) {
    var cliptext = '';
    $.each(clipboard, function(i,txt) { cliptext += txt + "\n"; });
    $('<div id="selection_clipboard_contents"></div>')
        .html('<pre>'+cliptext+'</pre>')
        .dialog({
            autoOpen: true,
            appendTo: '.selection_command[tabid="'+tabid+'"]',
            width: '800px',
//            position: {my: 'right top', at: 'bottom left', of: '#clip' },
            title: 'Clipboard contents',
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
            },
//            open: function(event, ui) {
//            }
        })
        .show();
}
function update_saved_info(tabid, is_saved) {
    selection_is_saved = is_saved;
    if(is_saved) {
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save"]').attr('disabled', 'disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save_show"]').attr('disabled', 'disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_reload"]').attr('disabled', 'disabled');
        $('.selection_command[tabid="'+tabid+'"] span[class="selection_span_info"]').hide();
        $('.selection_edit[tabid="'+tabid+'"] textarea').one('input propertychange', function() { update_saved_info(tabid, false); });
    } else {
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save"]').removeAttr('disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save_show"]').removeAttr('disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_reload"]').removeAttr('disabled');
    }
}
function save_markup(tabid, selection_id) {
    var markup = $('.selection_edit[tabid="'+tabid+'"] textarea').val();
    $.ajax({
        async : false,
        type: 'POST',
        url: "action.php?tpl=custom_view_selections",
        data : { "action" : "save_markup", "selection_id" : selection_id, "markup" : markup },
        dataType: 'json',
        cache: false,
        complete : function (r) {
            if(!r.status) {
                notify_ko('Error, can\'t retrieve data from server !');
            } else {
                notify_ok("Selection saved");
                update_saved_info(tabid, true);
            }
        }
    });
}
function load_markup(tabid, selection_id) {
    $.ajax({
        async : false,
        type: 'POST',
        url: "action.php?tpl=custom_view_selections",
        data : { "action" : "load_tab", "selection_id" : selection_id },
        dataType: 'json',
        cache: false,
        success: function(result, textStatus, XMLHttpRequest) {
            if(result['markup']) {
                $('.selection_edit[tabid="'+tabid+'"] textarea').val(result['markup']);
                update_saved_info(tabid, true);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            var error =  jQuery.parseJSON(XMLHttpRequest['responseText']);
            notify_ko('jsonrpc error : '+error['error']['message']+' (code : '+error['error']['code']+')');
        }
    });
}

function retreive_graphs_list(graph_vars) {
//TODO : move grouped_types in a cache (no need to reload at every call)
    var grouped_types = get_grouped_types();
    var all_hosts = [];
    var all_graphs = [];
    if(graph_vars['host'].match(/[\*,\+\[\]\$]/)) {
        var regex = new RegExp(graph_vars['host']);
        $.ajax({
            async: false,
            type: 'POST',
            url: 'action.php?tpl=jsonrpc&cdsrc='+graph_vars['cdsrc'],
            data: JSON.stringify({"jsonrpc": "2.0", "method": "pw_get_dir_hosts", "params": "", "id": 0}),
            dataType: 'json',
            cache: false, 
            success: function(result, textStatus, XMLHttpRequest) {
                if(result['result'] && result['result']['values']) {
                    $(result['result']['values']).each(function(i, host) {
                            if(regex.test(host)) {
                                all_hosts.push(host);
                            }
                        });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var error =  jQuery.parseJSON(XMLHttpRequest['responseText']);
                notify_ko('jsonrpc error : '+error['error']['message']+' (code : '+error['error']['code']+')');
            },
        });
    } else {
        all_hosts.push(graph_vars['host']);
    } 
    if(all_hosts.length > 0) {
        var rp;
        var rpi;
        var rt;
        var rti;
        if(graph_vars['plugin']                && graph_vars['plugin'].match(/[\*,\+\[\]\$]/))            { rp = new RegExp(graph_vars['plugin']); }
        if(graph_vars['plugininstance']    && graph_vars['plugininstance'].match(/[\*,\+\[\]\$]/))    { rpi = new RegExp(graph_vars['plugininstance']); }
        if(graph_vars['type']                && graph_vars['type'].match(/[\*,\+\[\]\$]/))                { rp = new RegExp(graph_vars['type']); }
        if(graph_vars['typeinstance']        && graph_vars['typeinstance'].match(/[\*,\+\[\]\$]/))    { rpi = new RegExp(graph_vars['typeinstance']); }
        $(all_hosts).each(function(i, host) {
            if((typeof rp === "undefined")
                    && (typeof rpi === "undefined")
                    && (typeof rt === "undefined")
                    && (typeof rti === "undefined")
              ) {
                var check_grouped_type = jQuery.inArray(graph_vars['type'], grouped_types);
                all_graphs.push({
                    "cdsrc": graph_vars['cdsrc'],
                    "host": host,
                    "plugin": graph_vars['plugin'],
                    "plugin_instance": graph_vars['plugininstance']?graph_vars['plugininstance']:"",
                    "type": graph_vars['type'],
                    "type_instance": (check_grouped_type == -1)?(graph_vars['typeinstance']?graph_vars['typeinstance']:""):""
                    });
            } else {
                $.ajax({
                    async: false,
                    type: 'POST',
                    url: 'action.php?tpl=jsonrpc&cdsrc='+graph_vars['cdsrc'],
                    data: JSON.stringify({"jsonrpc": "2.0", "method": "pw_get_dir_all_rrds_for_host", "params": { "hostname" : host }, "id": 0}),
                    dataType: 'json',
                    cache: false, 
                    success: function(result, textStatus, XMLHttpRequest) {
                        if(result['result'] && result['result']['values']) {
                            $.each(result['result']['values'], function(p, p_c) {
                                    if(((typeof rp !== "undefined") && rp.test(p)) || (graph_vars['plugin'] && (graph_vars['plugin'] === p))) {
                                        $.each(p_c, function(pi, pi_c) {
                                            if(((typeof rpi !== "undefined") && rpi.test(pi)) || (graph_vars['plugininstance'] && (graph_vars['plugininstance'] === pi)) || (!graph_vars['plugininstance'] && !pi)) {
                                                $.each(pi_c, function(t, t_c) {
                                                    var check_grouped_type = jQuery.inArray(t, grouped_types);
                                                    if (check_grouped_type == -1) {
                                                        if(((typeof rt !== "undefined") && rt.test(t)) || (graph_vars['type'] && (graph_vars['type'] === t))) {
                                                            $.each(t_c, function(ti, ti_c) {
                                                                if(((typeof rti !== "undefined") && rti.test(ti)) || (graph_vars['typeinstance'] && (graph_vars['typeinstance'] === ti)) || (!graph_vars['typeinstance'] && !ti)) {
                                                                    all_graphs.push({
                                                                        "cdsrc": graph_vars['cdsrc'],
                                                                        "host": host,
                                                                        "plugin": p,
                                                                        "plugin_instance": (typeof pi === "undefined")?"":pi,
                                                                        "type": t,
                                                                        "type_instance": (typeof ti === "undefined")?"":ti
                                                                    });
                                                                }
                                                            });
                                                        }
                                                    } else {
                                                        all_graphs.push({
                                                            "cdsrc": graph_vars['cdsrc'],
                                                            "host": host,
                                                            "plugin": p,
                                                            "plugin_instance": (typeof pi === "undefined")?"":pi,
                                                            "type": t,
                                                            "type_instance": ""
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
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
    return(all_graphs);
}
function switch_to_show(tabid) {
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_edit"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_reload"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_show"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save_show"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_paste"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_showclip"]').hide();
    if(selection_is_saved) {
            $('.selection_command[tabid="'+tabid+'"] span[class="selection_span_info"]').hide();
    } else {
            $('.selection_command[tabid="'+tabid+'"] span[class="selection_span_info"]').show();
    }
    $('.selection_edit[tabid="'+tabid+'"]').hide();
    $('.selection_show[tabid="'+tabid+'"]').show();

    var converter = new Showdown.converter({ extensions: ['rrdgraph'] });
    var markup = $('.selection_edit[tabid="'+tabid+'"] textarea').val();
    var html = converter.makeHtml(markup);
    $('.selection_show[tabid="'+tabid+'"]').html(html);
    var graphid=0;
    $('.selection_show[tabid="'+tabid+'"] span[class="rrdgraph_to_render"]').each(function(idx) {
        var item_current = $(this);
        var code=decodeURIComponent($(this).attr('rrdgraph'));
        var graph_vars = [];
        $(this).removeAttr('rrdgraph');
        $(this).text("Loading...");
        try {
            graph_vars = $.parseJSON(code);
        } catch(e) {
            console.log(e);
            console.log("json was :\n"+code);
        }

        if(graph_vars['host']) {
            var all_graphs = retreive_graphs_list(graph_vars);
            $.each(all_graphs, function(i, g) {
                var item_graph = $('<img class="graph" id="graph_'+tabid+'_'+graphid+'" zone="tab"/>');
                item_graph.insertAfter(item_current);
                item_graph.pwgraph(g).pwgraph('display');
                item_current = item_graph;

                graphid++;
            });

        }
        $(this).remove();
    });
}

function switch_to_edit(tabid) {
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_edit"]').hide();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_reload"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_show"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_save_show"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_paste"]').show();
    $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_showclip"]').show();
    $('.selection_command[tabid="'+tabid+'"] span[class="selection_span_info"]').hide();
    $('.selection_edit[tabid="'+tabid+'"]').show();
    $('.selection_show[tabid="'+tabid+'"]').hide();

    if(clipboard.length) {
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_paste"]').removeAttr('disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_showclip"]').removeAttr('disabled');
    } else {
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_paste"]').attr('disabled', 'disabled');
        $('.selection_command[tabid="'+tabid+'"] input[class="selection_btn_showclip"]').attr('disabled', 'disabled');
    }

    $('#timebutton').hide();
    $('#datetime').hide();
    $('#timespan').hide();
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
