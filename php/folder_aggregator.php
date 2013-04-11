<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

require 'lib/class.folder_aggregator.php';

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

if (!isset($_POST['action'])) { die('No action submited !'); }

$jstree = new json_tree($view_id);
$res = $jstree->_get_node($id);
$owidget = new folder_aggregator($res);
switch($_POST['action']) {
    case 'add_plugin':
        $owidget->add_plugin($_POST['plugin'], $_POST['cf']);
        break;
    case 'del_plugin':
        $owidget->del_plugin($_POST['plugin']);
        break;
    case 'get_hosts':
        $hosts = array();
        $data = $jstree->_get_children($id, true);
        foreach($data as $host) {
            if ($host['type'] == 'default') { $hosts[] = $host['title']; }
        }
        echo json_encode($hosts);
        break;
    default:
        die('No valid action submited !');
        break;
}

?>
