/**
 * Import/export functions for Perfwatcher
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


function tree_import (id) {
    alert("Import " + id);
}

function tree_export (id, name) {
    var pwgraph_hover_enabled_prev = pwgraph_hover_enabled;
    hide_graph_helpers();
    pwgraph_hover_enabled = false;
    $('<div id="modaldialogcontents"></div>')
        .html('<p>Export...</p>')
        .dialog({
            autoOpen: true,
            appendTo: '#modaldialog',
            title: 'Export tool ('+name+')',
            close: function(event,ui) {
                $(this).dialog('destroy').remove();
                $('#modaldialog').hide();
                $('#modaldialogcontents').html("");
                pwgraph_hover_enabled = pwgraph_hover_enabled_prev;
            },
            open: function(event, ui) {
                $('#modaldialog').show();
                $('#modaldialogcontents').html(ich.export_box({ 'name': name, 'id': id, 'view_id': view_id }));
            }
        });
}

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
