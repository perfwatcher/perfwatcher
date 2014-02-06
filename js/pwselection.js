/**
 * Selections helper functions for Perfwatcher
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

(function( $ ){

  var options = {
    pwtabid              : 0,
    selection_id       : 0,
    selection_is_saved : true
  }

  var methods = {
    init : function( initoptions ) { 
	  var myoptions = options;
	  $.extend(myoptions, initoptions);
      myoptions['grouped_types'] = get_grouped_types();
	  this.data(myoptions);
      return this;
    },

    show_clipboard_contents: function () {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        var cliptext = '';
        $.each(clipboard, function(i,txt) { cliptext += txt + "\n"; });
        $('<div id="selection_clipboard_contents"></div>')
            .html('<pre>'+cliptext+'</pre>')
            .dialog({
                autoOpen: true,
                appendTo: '.selection_command[pwtabid="'+pwtabid+'"]',
                width: '800px',
//                position: {my: 'right top', at: 'bottom left', of: '#clip' },
                title: 'Clipboard contents',
                close: function(event,ui) {
                    $(this).dialog('destroy').remove();
                },
//                open: function(event, ui) {
//                }
            })
            .show();
      return this;
    },
    update_saved_info: function (is_saved) {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        options['selection_is_saved'] = is_saved;
        this.data(options);
        current_selection = '.selection_command[pwtabid="'+pwtabid+'"]';
        
        if(is_saved) {
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save"]').attr('disabled', 'disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save_show"]').attr('disabled', 'disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_reload"]').attr('disabled', 'disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] span[class="selection_span_info"]').hide();
            $('.selection_edit[pwtabid="'+pwtabid+'"] textarea').one('input propertychange', function() { $(current_selection).pwselection('update_saved_info', false); });
        } else {
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save"]').removeAttr('disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save_show"]').removeAttr('disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_reload"]').removeAttr('disabled');
        }
      return this;
    },
    save_markup: function () {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        var selection_id = options['selection_id'];
        var markup = $('.selection_edit[pwtabid="'+pwtabid+'"] textarea').val();
        current_selection = '.selection_command[pwtabid="'+pwtabid+'"]';
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
                    $(current_selection).pwselection('update_saved_info', true);
                }
            }
        });
      return this;
    },
    load_markup: function () {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        var selection_id = options['selection_id'];
        current_selection = '.selection_command[pwtabid="'+pwtabid+'"]';
        $.ajax({
            async : false,
            type: 'POST',
            url: "action.php?tpl=custom_view_selections",
            data : { "action" : "load_tab", "selection_id" : selection_id },
            dataType: 'json',
            cache: false,
            success: function(result, textStatus, XMLHttpRequest) {
                if(result['markup']) {
                    $('.selection_edit[pwtabid="'+pwtabid+'"] textarea').val(result['markup']);
                    $(current_selection).pwselection('update_saved_info', true);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var error =  jQuery.parseJSON(XMLHttpRequest['responseText']);
                notify_ko('jsonrpc error : '+error['error']['message']+' (code : '+error['error']['code']+')');
            }
        });
      return this;
    },
    retreive_graphs_list: function (graph_vars) {
	    var options = this.data();
        var grouped_types = options['grouped_types'];
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
    },
    switch_to_show: function () {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_edit"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_reload"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_show"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save_show"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_paste"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_showclip"]').hide();
        if(options['selection_is_saved']) {
                $('.selection_command[pwtabid="'+pwtabid+'"] span[class="selection_span_info"]').hide();
        } else {
                $('.selection_command[pwtabid="'+pwtabid+'"] span[class="selection_span_info"]').show();
        }
        $('.selection_edit[pwtabid="'+pwtabid+'"]').hide();
        $('.selection_show[pwtabid="'+pwtabid+'"]').show();
    
        var converter = new Showdown.converter({ extensions: ['rrdgraph'] });
        var markup = $('.selection_edit[pwtabid="'+pwtabid+'"] textarea').val();
        var html = converter.makeHtml(markup);
        $('.selection_show[pwtabid="'+pwtabid+'"]').html(html);
        var graphid=0;
        $('.selection_show[pwtabid="'+pwtabid+'"] span[class="rrdgraph_to_render"]').each(function(idx) {
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
                var all_graphs = $(this).pwselection('retreive_graphs_list', graph_vars);
                $.each(all_graphs, function(i, g) {
                    var item_graph = $('<img class="graph" id="graph_'+pwtabid+'_'+graphid+'" zone="tab"/>');
                    item_graph.insertAfter(item_current);
                    item_graph.pwgraph(g).pwgraph('display');
                    item_current = item_graph;
    
                    graphid++;
                });
    
            }
            $(this).remove();
        });
      return this;
    },
    switch_to_edit: function () {
	    var options = this.data();
        var pwtabid = options['pwtabid'];
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_edit"]').hide();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_reload"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_show"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_save_show"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_paste"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_showclip"]').show();
        $('.selection_command[pwtabid="'+pwtabid+'"] span[class="selection_span_info"]').hide();
        $('.selection_edit[pwtabid="'+pwtabid+'"]').show();
        $('.selection_show[pwtabid="'+pwtabid+'"]').hide();
    
        if(clipboard.length) {
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_paste"]').removeAttr('disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_showclip"]').removeAttr('disabled');
        } else {
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_paste"]').attr('disabled', 'disabled');
            $('.selection_command[pwtabid="'+pwtabid+'"] input[class="selection_btn_showclip"]').attr('disabled', 'disabled');
        }
    
        hide_graph_helpers();
        return this;
    }
  };

  $.fn.pwselection = function( method ) {
    // Method calling logic
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.pwselection' );
    }    
  
  };
})( jQuery );

// vim: set filetype=javascript fdm=marker sw=4 ts=4 et:
