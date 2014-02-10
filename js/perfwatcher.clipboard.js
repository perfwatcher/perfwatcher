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
        hide_graph_helpers();
    });
}

function clipboard_refresh_view() {
    var pwrole = $('#clipboard_switch_markdown_btn').attr('pwrole');
    if(pwrole == "markdown") {
        $('#clipboard_switch_markdown_btn').text('Show graphs');
        $('#modalcliplist ul .clipboard_string').show();
        $('#modalcliplist ul .rm_from_clipboard').hide();
        $('#modalcliplist ul img.graph').hide();
        hide_graph_helpers();
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
            appendTo: '#modaldialog',
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
                hide_graph_helpers();
            },
            open: function(event, ui) {
                pwgraph_current_zone = "clip";
                clipboard_prepare_dialog();
                clipboard_refresh_view();
            }
        })
        .show();
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
