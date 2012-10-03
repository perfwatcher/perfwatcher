<?php
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

if (!isset($_GET['id']) and !isset($_POST['id'])) {
    die('Error : POST or GET id missing !!');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
} else {
    die('Error : No valid id found !!!');
}

$jstree = new json_tree();
$res = $jstree->_get_node($id);
$datas = $jstree->get_datas($res['id']);

if (isset($_POST['action']) || isset($_GET['action'])) {
    switch (isset($_POST['action']) ? $_POST['action'] : $_GET['action']) {
		case 'get_datas':
				$hostlist = array();
				$childrens = $jstree->_get_children($id, true);
				foreach($childrens as $children) {
					if ($children['type'] == 'default') {
						$hostlist[] = $children['title'];
					}
				}
//				$plugins = get_childrens_plugins($jstree,"aggregator_$id");
				sort(&$hostlist);
//				sort(&$plugins);
				echo json_encode(array(
					'hosts' => $hostlist,
//					'plugins' => $plugins
				));
		break;
        case 'save_tab':
            $datas['tabs'][$_POST['tab_id']]['selected_graph'] = $_POST['selected_graph'];
            $datas['tabs'][$_POST['tab_id']]['selected_hosts'] = $_POST['selected_hosts'];
            $datas['tabs'][$_POST['tab_id']]['selected_aggregators'] = $_POST['selected_aggragators'];
            $jstree->set_datas($id, $datas);
        break;
    }
}

?>

