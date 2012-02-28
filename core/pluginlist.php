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
//echo "<pre>".print_r($_POST, true)."</pre>";
$jstree = new json_tree();
$datas = $jstree->get_datas($_GET['id']);
if (isset($_POST['action']) && ereg("/admin/", $_SERVER["REQUEST_URI"])) {
    switch($_POST['action']) {
        case 'add_plugin':
            if (!isset($datas['plugins'])) { $datas['plugins'] = array(); }
            if (
                isset($_POST['type'])
                && !isset($datas['plugins'][$_POST['plugin'][0].'-'.$_POST['type']])
            ) {
                $datas['plugins'][$_POST['plugin'][0].'-'.$_POST['type']] = array();
                $jstree->set_datas($_GET['id'], $datas);
            }
        break;
        case 'del_plugin':
            if(isset($datas['plugins'][$_POST['plugin']])) {
                unset($datas['plugins'][$_POST['plugin']]);
                $jstree->set_datas($_GET['id'], $datas);
            }
        break;
    }
}
//echo "<pre>".print_r($datas, true)."</pre>";
$res = $jstree->_get_node($_GET['id']);
$tpl->assign('dbdata', $res);
if (isset($datas['plugins'])) {
    ksort($datas['plugins']);
} else {
    $datas['plugins'] = array();
}

$tpl->assign('plugins', $datas['plugins']);


?>
