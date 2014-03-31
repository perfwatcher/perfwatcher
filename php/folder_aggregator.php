<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2012 Cyril Feraudet
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
 * @copyright 2012 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

require 'lib/class.folder_aggregator.php';

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);

if (!isset($_POST['action'])) { die('No action submited !'); }

$jstree = new json_tree($view_id);
$res = $jstree->_get_node($id);
$owidget = new folder_aggregator($res);
switch($_POST['action']) {
    case 'get_collectd_sources':
        $current_cdsrc = $jstree->get_node_collectd_source($id);
        if($current_cdsrc == "Auto-detect") {
            echo json_encode(array());
            break;
        }
        $children_cdsrc = array();
        if(is_aggregator_allowed($current_cdsrc)) {
            $children_cdsrc[$current_cdsrc] = 1;
        }
        $data = $jstree->_get_children($id, true, "", "", $current_cdsrc);
        foreach($data as $host) {
            if($host['CdSrc'] && is_aggregator_allowed($host['CdSrc'])) $children_cdsrc[$host['CdSrc']] = 1;
        }
        echo json_encode(array_keys($children_cdsrc));
        break;
    case 'add_plugin':
        $owidget->add_aggregator($collectd_source, $_POST['plugin'], $_POST['cf']);
        break;
    case 'del_plugin':
        $owidget->del_aggregator($collectd_source, $_POST['plugin']);
        break;
    case 'get_hosts':
        $hosts = array();
        $data = $jstree->_get_children($id, true, "", "", $collectd_source);
        foreach($data as $host) {
            if ($host['pwtype'] == 'server') { $hosts[] = $host['title']; }
        }
        echo json_encode($hosts);
        break;
    default:
        die('No valid action submited !');
        break;
}

?>
