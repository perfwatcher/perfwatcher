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
var click = 0;
time_begin = 0;
time_end = 0;
function nav_init (time_beginf, time_endf) {
    time_begin = time_beginf;
    time_end = time_endf;
    $('.graph_image').each(function () {
        $(this).attr('ssrc', $(this).attr('isrc').replace (/&(begin|end)=[^&]*/g, ''));
        $(this).click(handle_click);
        $(this).attr('navTimeBegin', new Number (time_begin));
        $(this).attr('navTimeEnd', new Number (time_end));
    });
}

function nav_image_repaint (id)
{
    $('#'+id).attr('src', $('#'+id).attr('ssrc') + '&' 
    + 'begin=' + parseInt($('#'+id).attr('navTimeBegin')).toFixed(0)
    + '&'
    + 'end=' + parseInt($('#'+id).attr('navTimeEnd')).toFixed(0)
    );
} 

function nav_time_reset (id ,diff)
{
  $('#'+id).attr('navTimeEnd', new Number ((new Date ()).getTime () / 1000));
  $('#'+id).attr('navTimeBegin', new Number (parseInt($('#'+id).attr('navTimeEnd')) - diff));
  nav_image_repaint (id);
  return (true);
}

function nav_time_change_obj (id, factor_begin, factor_end)
{
  var diff;

    navTimeBegin = parseInt($('#'+id).attr('navTimeBegin'));
    navTimeEnd = parseInt($('#'+id).attr('navTimeEnd'));
    diff = navTimeEnd - navTimeBegin;
    
    /* Prevent zooming in if diff is less than five minutes */
    if ((diff <= 300) && (factor_begin > 0.0) && (factor_end < 0.0))
      return (true);
    
    navTimeBegin += (diff * factor_begin);
    navTimeEnd   += (diff * factor_end);

    $('#'+id).attr('navTimeBegin', navTimeBegin);
    $('#'+id).attr('navTimeEnd', navTimeEnd);

    nav_image_repaint (id);
    
    return (true);
}

function nav_time_change (id, factor_begin, factor_end)
{
  var diff;

  if (id == '*')
  {
    var all_images;
    var i;

    all_images = document.getElementsByTagName ("img");
    for (i = 0; i < all_images.length; i++)
    {
      if (all_images[i].className != "graph_image")
        continue;
    
      nav_time_change_obj (all_images[i].id, factor_begin, factor_end);
    }
  }
  else
  {
    var img;

    nav_time_change_obj (id, factor_begin, factor_end);
  }

  return (true);
} /* nav_time_change */

function nav_move_earlier (img_id)
{
  return (nav_time_change (img_id, -0.2, -0.2));
} /* nav_move_earlier */

function nav_move_later (img_id)
{
  return (nav_time_change (img_id, +0.2, +0.2));
} /* nav_move_later */

function nav_zoom_in (img_id)
{
  return (nav_time_change (img_id, +0.2, -0.2));
} /* nav_zoom_in */

function nav_zoom_out (img_id)
{
  return (nav_time_change (img_id, (-1.0 / 3.0), (1.0 / 3.0)));
} /* nav_zoom_in */

function nav_set_reference (id)
{
    $(".graph_image").each(function() {
        $(this).attr('navTimeBegin', $('#'+id).attr('navTimeBegin'));
        $(this).attr('navTimeEnd', $('#'+id).attr('navTimeEnd'));
        if ($(this).attr('src') != undefined) {
            nav_image_repaint(this.id);
        }
    });
} 

function nav_recenter (e)
{
    var x;
    var y;
    var diff;
    var time_old_center;
    var time_new_center;
    var width;
    var navTimeEnd, navTimeBegin;
    width = parseInt($('#'+e.target.id).css('width')) - 97;
    navTimeBegin = parseInt($('#'+e.target.id).attr('navTimeBegin'));
    navTimeEnd = parseInt($('#'+e.target.id).attr('navTimeEnd'));
    
    x = e.originalEvent.layerX - 66;
    if (!x || (x < 0) || (x > width))
      return;
    
    y = e.originalEvent.layerY;
    if (!y || (y < 30) || (y > 155))
      return;
    
    diff = navTimeEnd - navTimeBegin;
    time_old_center = navTimeBegin + (diff / 2.0);
    time_new_center = navTimeBegin + (x * diff / width);
    
    $('#'+e.target.id).attr('navTimeBegin', navTimeBegin + (time_new_center - time_old_center));
    $('#'+e.target.id).attr('navTimeEnd', navTimeEnd + (time_new_center - time_old_center));
}

function handle_click (e)
{
    click++
    if (click > 1) { click = 0; nav_handle_dblclick (e); }
    setTimeout(function () {
        if (click == 1) {
            click = 0;
            nav_handle_click (e);
        }
        click = 0;
    }, 300);
}

function nav_handle_dblclick (e)
{
    nav_recenter (e);
    nav_image_repaint (e.target.id)
}

var selecttimespan = 0;
var selecttimespanfunc = false;
var selecttimespangraph = "";

function nav_handle_click (e)
{
    if (selecttimespan == 0) {
        selecttimespan = 1;
        $('#selecttimespan').css('position', 'absolute');
        $('#selecttimespan').css('top', $(e.currentTarget).offset().top + 31);
        if (e.pageX - $(e.currentTarget).offset().left - 67 < 0) {
             $('#selecttimespan').css('left', $(e.currentTarget).offset().left + 67);
        } else if (e.pageX - $(e.currentTarget).offset().left - 67 + 80 > 602) {
             $('#selecttimespan').css('left', $(e.currentTarget).offset().left + 67 + 602 - 80);
        } else {
            $('#selecttimespan').css('left', e.clientX);
        }
        $('#selecttimespan').css('width', 80);
        $('#selecttimespan').resizable({
            autoHide:   true,
            minHeight:  122,
            maxHeight:  122,
            maxWidth:   (602 - (e.pageX - $(e.currentTarget).offset().left - 67)),
            minWidth:   20,
            stop: function(e2, ui) {
                $('#selecttimespan').draggable('option', 'containment', [
                    $(e.currentTarget).offset().left + 66,
                    $(e.currentTarget).offset().top + 30,
                    $(e.currentTarget).offset().left + 669 - parseInt($('#selecttimespan').css('width')),
                    $(e.currentTarget).offset().top + 30
                ]
                );
            }
        });
        $('#selecttimespan').draggable({
            axis: "x",
            appendTo: e.currentTarget,
            containment: [
                $(e.currentTarget).offset().left + 66,
                $(e.currentTarget).offset().top + 30,
                $(e.currentTarget).offset().left + 669 - parseInt($('#selecttimespan').css('width')),
                $(e.currentTarget).offset().top + 30
            ],
            stop: function() {
                $('#selecttimespan').resizable('option', 'maxWidth', 
                $(e.currentTarget).offset().left 
                + e.currentTarget.clientWidth 
                - parseInt($('#selecttimespan').css('left')) 
                -29
                );
            }
        });
        if (selecttimespangraph != '' && selecttimespangraph != e.currentTarget.id) {
            $('#selecttimespan').off('dblclick');
            selecttimespanfunc = false;
        }
        if (!selecttimespanfunc) {
            selecttimespanfunc = true;
            selecttimespangraph = e.currentTarget.id;
            $('#selecttimespan').dblclick(function(){
                var navtimebegin = parseInt($('#'+e.currentTarget.id).attr('navtimebegin'));
                var navtimeend = parseInt($('#'+e.currentTarget.id).attr('navtimeend'));
                var timespan = navtimeend - navtimebegin;
                var width = parseInt($(this).css('width'));
                var start = Math.ceil(parseInt($('#selecttimespan').css('left')) - $(e.currentTarget).offset().left - 66) ;
                var end = Math.ceil(parseInt($('#selecttimespan').css('left')) - $(e.currentTarget).offset().left - 66 + width);
                var offsetbegin = timespan / 602 * start;
                var offsetend = timespan / 602 * end;
                if (new Number(Math.ceil(navtimebegin + offsetbegin + 60)) < new Number(Math.ceil(navtimebegin + offsetend))) {
                    $('#'+e.currentTarget.id).attr('navtimebegin', new Number(Math.ceil(navtimebegin + offsetbegin)));
                    $('#'+e.currentTarget.id).attr('navtimeend', new Number(Math.ceil(navtimebegin + offsetend)));
                    nav_image_repaint (e.currentTarget.id);
                }
                selecttimespan = 0;
                $('#selecttimespan').hide();
            });
        }
        $('#selecttimespan').show();
    } else { 
        selecttimespan = 0;
        $('#selecttimespan').hide();

    }
}

function nav_custom_date(id) {
  $("#date"+id).datepicker({
        onSelect: function(dateText, inst) {
            $('#'+id).attr('navTimeBegin', new Number (dateText / 1000));
            $('#'+id).attr('navTimeEnd', new Number (parseInt($('#'+id).attr('navTimeBegin')) + 86400));
            nav_image_repaint (id);
            $("#date"+id).datepicker("destroy");
        },
        dateFormat: '@',
        showOtherMonths: true,
        selectOtherMonths: true,
        numberOfMonths: 4,
        showButtonPanel: true,
        maxDate: '+0d',
        gotoCurrent: true
     }); 
  return (true);
}

function nav_toggle_legend (img_id) {
    // &tinylegend=1
    img = document.getElementById (img_id);
    var pattern = /tinylegend/;
    if(pattern.test(img.src)) {
        img.src = img.navBaseURL;
    } else {
        img.src = img.navBaseURL + '&tinylegend=1';
    }
}

function add_graph(parenttab, host, plugin_name, plugin_instance_name, type_name, type_instance_name, graph_id) {
    var graph_float = "<div class=\"graph_container\"><div class=\"graph_float\" \
                  parenttab=\""+parenttab+"\" \
                  plugin_name=\""+plugin_name+"\" \
                  plugin_instance_name=\""+plugin_instance_name+"\" \
                  type_name=\""+type_name+"\" \
                  type_instance_name=\""+type_instance_name+"\" \
                > \
                  <img id=\"graph"+graph_id+"\" class=\"graph_image\" \
                    isrc=\"graph.php?host="+host+"&amp;plugin="+plugin_name+"&amp;plugin_instance="+plugin_instance_name+"&amp;type="+type_name+"&amp;type_instance="+type_instance_name+"&amp;begin=-86400&amp;width=500&amp;"+Math.round((new Date()).getTime() / 1000)+"\" \
                    host=\""+host+"\" \
                    parenttab=\""+parenttab+"\" \
                    plugin_name=\""+plugin_name+"\" \
                    plugin_instance_name=\""+plugin_instance_name+"\" \
                    type_name=\""+type_name+"\" \
                    type_instance_name=\""+type_instance_name+"\" \
                  /> \
                  <div class=\"controls zoom\"> \
                    <div title=\"Earlier\" onclick=\"nav_move_earlier ('graph"+graph_id+"');\">&#x2190;</div> \
                    <div title=\"Zoom out\" onclick=\"nav_zoom_out ('graph"+graph_id+"');\">-</div> \
                    <div title=\"Zoom in\" onclick=\"nav_zoom_in ('graph"+graph_id+"');\">+</div> \
                    <div title=\"Later\" onclick=\"nav_move_later ('graph"+graph_id+"');\">&#x2192;</div> \
                  </div> \
                  <div class=\"controls preset\"> \
                    <div title=\"Show current hour\" onclick=\"nav_time_reset ('graph"+graph_id+"', 3600);\">H</div> \
                    <div title=\"Show current day\" onclick=\"nav_time_reset ('graph"+graph_id+"', 86400);\">D</div> \
                    <div title=\"Show current week\" onclick=\"nav_time_reset ('graph"+graph_id+"', 7 * 86400);\">W</div> \
                    <div title=\"Show current month\" onclick=\"nav_time_reset ('graph"+graph_id+"', 31 * 86400);\">M</div> \
                    <div title=\"Show current year\" onclick=\"nav_time_reset ('graph"+graph_id+"', 366 * 86400);\">Y</div> \
                    <div title=\"Custom date\" onclick=\"nav_custom_date ('graph"+graph_id+"');\">C</div> \
                    <div title=\"Set all graph to this timespan\" onclick=\"nav_set_reference ('graph"+graph_id+"');\">!</div> \
                  </div> \
        </div> \
        <div id=\"dategraph"+graph_id+"\"></div> </div>";
        $(graph_float).appendTo('#'+parenttab);
        $('#graph' + graph_id).attr('src', $('#graph' + graph_id).attr('isrc'));
        $('#graph' + graph_id).attr('ssrc', $('#graph' + graph_id).attr('isrc'));
        $('#graph' + graph_id).attr('navTimeBegin', time_begin);
        $('#graph' + graph_id).attr('navTimeEnd', time_end);
        $('#graph' + graph_id).click(handle_click);
        $('#graph' + graph_id).contextMenu({
            menu: 'graphmenu'
        },
            function(action, el, pos) {
            switch(action) {
                case 'top':
                    var timestamp = 0;
                    var navtimebegin = new Number($(el).attr('navtimebegin'));
                    var navtimeend = new Number($(el).attr('navtimeend'));
                    var host = $(el).attr('host');
                    if (pos.x < 67) {
                        timestamp = navtimebegin;
                    } else if (pos.x > 677) {
                        timestamp = navtimeend;
                    } else {
                        timestamp = Math.ceil(navtimebegin + ((navtimeend - navtimebegin) / 600 * (pos.x - 67)));
                    }
                    $.get('index.php?tpl=top&host='+host+'&timestamp='+timestamp, function(data) {
                        $('#topdiv').html(data);
                        $('#topdiv').dialog({ height: 530, width: 530 });
                    });
                break;
                default:
                    alert('Available soon ...');
                break;
            }
        });

}

function updatetop(host, timestamp, sort) {
    $.get('index.php?tpl=top&host='+host+'&timestamp='+timestamp+'&sort='+sort, function(data) {
        $('#topdiv').html(data);
    });
}

/* vim: set sw=4 sts=4 et : */
