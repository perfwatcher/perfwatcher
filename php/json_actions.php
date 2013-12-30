<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
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
 */ 

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
        echo json_encode(array());
        break;
    case 'del_tab':
        $selection_id = get_arg('selection_id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
        selection_delete($selection_id);
        echo json_encode(array());
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
