<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
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

header("HTTP/1.0 200 OK");
header('Content-type: text/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

$jstree = new json_tree($view_id);
$res = $jstree->_get_node($id);

$datas = $jstree->get_datas($res['id']);
if(isset($datas['CdSrc'])) {
    $collectd_source = $datas['CdSrc'];
    $collectd_source_is_inherited = 0;
} else {
    $collectd_source = $jstree->get_node_collectd_source($id);
    $collectd_source_is_inherited = 1;
}

switch ($res['pwtype']) {
    case 'server' :
        $host = $res['title'];
        $plugins = get_list_of_rrds($collectd_source, $host);
        $aggregators = array();
        break;
    case 'container' :
        $host = 'aggregator_'.$res['id'];
        $plugins = array();
        $aggregators = array();
        foreach ($collectd_sources as $cdid => $cdsrc) {
            $a = get_list_of_rrds($cdid, $host);
            if(count($a) > 0) {
                $aggregators[$cdid] = $a;
            }
        }
        break;
    case 'selection' :
        $host = $res['title'];
        $plugins = array();
        $aggregators = array();
        break;
    default:
        die('Error : node not found !!!');
        break;
}


$rv = json_encode(
        array(
            'host' => $host,
            'plugins' => $plugins,
            'aggregators' => $aggregators,
            'jstree' => $res,
            'datas' => $datas,
            'config' => array(
                'widgets' => get_widget($res),
                'CdSrc' => array(
                    'source' => $collectd_source,
                    'inherited' => $collectd_source_is_inherited
                    )
                )
            ));
echo $rv;
?>
