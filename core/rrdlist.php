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

$tpl->assign('timestamp', time());

$jstree = new json_tree();
$res = $jstree->_get_node($id);
$datas = $jstree->get_datas($res['id']);

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_tab':
            if (!isset($datas['tabs'])) {
                $datas['tabs'] = array();
            }
            $id = md5(time().$_POST['tab_title']);
            $datas['tabs'][$id] = array('tab_title' => $_POST['tab_title'], 'selected_graph' => '');
            $jstree->set_datas($res['id'], $datas);
        break;
        case 'del_tab':
            unset($datas['tabs'][$_POST['tab_id']]);
            $jstree->set_datas($res['id'], $datas);
        break;
        case 'save_tab':
            $datas['tabs'][$_POST['tab_id']]['selected_graph'] = $_POST['selected_graph'];
            if (isset($_POST['selected_hosts'])) {
                $datas['tabs'][$_POST['tab_id']]['selected_hosts'] = $_POST['selected_hosts'];
            }
            $jstree->set_datas($res['id'], $datas);
        break;
    }
}

switch ($res['type']) {
    case 'default' :
        $host = $res['title'];
        $tpl->assign('nodetype', 'node');
    break;
    case 'folder' :
    case 'drive' :
        $host = 'aggregator_'.$res['id'];
        $tpl->assign('nodetype', 'container');
        list($nbhosts, $nbcontainers) = $jstree->get_children_count($res['id']);
        $tpl->assign('nbhosts', $nbhosts);
        $tpl->assign('nbcontainers', $nbcontainers);
        $nbaggregator = isset($datas['plugins']) ? count($datas['plugins']) + 1 : 0;
        $tpl->assign('nbaggregator', $nbaggregator);
    break;
    default:
        die('Error : node not found !!!');
    break;
}


$tpl->assign('nodeid', $res['id']);
$tpl->assign('process', get_process_count($res['id']));
$nbcpu = get_cpu_count($res['id']);
$load = get_load($host);
$tpl->assign('cpus', $nbcpu);
$tpl->assign('load', intval($load));
$tpl->assign('speedometer', intval($load/($nbcpu == 0 ? 1 : $nbcpu)*100));
$tpl->assign('host', $host);
$tpl->assign('dbdata', $res);
$tpl->assign('data', $datas);

$tpl->assign('timeyesterday',time() - 86400);
$tpl->assign('time',time());

$rrdtpls = scandir($tpl->template_dir);
foreach ($rrdtpls as $key => $rrdtpl) {
    if (substr($rrdtpl,0,8) != 'rrdlist-' || substr($rrdtpl,-5) != '.html') {
        unset($rrdtpls[$key]);
    } else {
        $rrdtpls[$key] = substr($rrdtpl,8,-5);
    }
}
$tpl->assign('rrdtpls', $rrdtpls);

if (is_dir($rrds_path.'/'.$host)) {
    $plugins = load_datas($host);
    $tpl->assign('plugins', $plugins);
} else {
    $tpl->assign('plugins', array());
}

$alltypes = array();
if (isset($plugins) && is_array($plugins)) {
		foreach($plugins as $plugin_name => $plugin) {
			foreach($plugin as $plugin_instance_name => $plugin_instance) {
				foreach($plugin_instance as $type_name => $type) {
					if (!isset($alltypes[$plugin_name])) { $alltypes[$plugin_name] = array(); }
					if (!in_array($type_name, $alltypes[$plugin_name])) { $alltypes[$plugin_name][] = $type_name; }
				}
			}
		}
}
$tpl->assign('alltypes', $alltypes);

?>
