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

if (!isset($_GET['action']) and !isset($_POST['action'])) {
    die('Error : POST or GET action missing !!');
}

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

$action_need_jstree = 0;
switch ($action) {
    case 'get_grouped_type':
        global $grouped_type;
        echo json_encode($grouped_type);
        break;
    case 'get_js':
        echo json_encode($extra_jsfile);
        break;
    case 'get_tabs':
        $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
        echo json_encode(selection_get_all_with_node_id($id));
        break;
    case 'add_tab':
        $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
        $deleteafter = 0;
        if (isset($_POST['lifetime']) && $_POST['lifetime'] > 0) {
            $deleteafter = time() + $_POST['lifetime'];
        }
        $selection_id = selection_create_new($_POST['tab_title'], $id, $deleteafter);
        echo json_encode(array("selection_id" => $selection_id));
        break;
    case 'del_tab':
        $selection_id = get_arg('selection_id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
        selection_delete($selection_id);
        echo json_encode(array());
        break;
    case 'reorder_tabs':
        $order = get_arg('order', array(), 0, "Error : no tabs", __FILE__, __LINE__);
        if(count($order)) {
            $i = 1; # 0 is reserved for unsorted (eg lasts) tabs
            $a = array();
            foreach ($order as $tabid) {
                $a{$tabid} = $i++;
            }
            selection_reorder($a);
        }
        break;
    case 'new_view':
        $view_title = get_arg('view_title', "no name", 0, "", __FILE__, __LINE__);
        list($id, $view_id) = create_new_view($view_title);
        echo json_encode(array( 'id' => $id, 'view_id' => $view_id ));
        break;
    case 'list_views':
        $maxrows = get_arg('maxrows', 10, 0, "", __FILE__, __LINE__);
        $startswith = get_arg('startswith', "", 0, "", __FILE__, __LINE__);
        $r = list_views($maxrows, $startswith);
        echo json_encode( $r );
        break;
    case 'delete_view':
        $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
        $r = delete_view($view_id);
        echo json_encode(array( 'view_id' => $r ));
        break;
    default:
        $action_need_jstree = 1;
}

if($action_need_jstree) {

    $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
    $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

    $jstree = new json_tree($view_id);
    $res = $jstree->_get_node($id);

    switch ($action) {
        case 'get_hosts': 
            $cdsrc = $jstree->get_node_collectd_source($id);
            $hosts = array();
            $children = $jstree->_get_children($id, true, "", "", $cdsrc);
            foreach($children as $host) {
                if ($host['pwtype'] == 'server') { $hosts[] = array('title' => $host['title'], 'CdSrc' => $host['CdSrc']); }
            }
            echo json_encode($hosts);
            break;
        case 'search':
            echo $jstree->searchfield($_GET['term']);
            break;
    }
}

?>
