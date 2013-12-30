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

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

$selection_id = get_arg('selection_id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);

$action_need_jstree = 0;
switch ($action) {
    case 'load_tab':
        echo json_encode(selection_get_data($selection_id));
        break;
    case 'save_markup':
        $markup = get_arg('markup', "", 0, "Error : No valid markup found !!!", __FILE__, __LINE__);
        selection_update_markup($selection_id, $markup);
        echo json_encode(array());
        break;
    default:
        $action_need_jstree = 1;
}


if($action_need_jstree) {
    
    $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
    $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
    
    $jstree = new json_tree($view_id);
    $res = $jstree->_get_node($id);
    $datas = $jstree->get_datas($res['id']);
    
#    if (isset($_POST['action'])) {
#        switch ($_POST['action']) {
#            case 'some_action':
#                break;
#        }
#    }
}

?>
