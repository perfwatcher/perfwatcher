<?php

require 'lib/class.folder_aggregator.php';

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

if (!isset($_POST['action'])) { die('No action submited !'); }

$jstree = new json_tree();
$res = $jstree->_get_node($id);
$owidget = new folder_aggregator($res);
switch($_POST['action']) {
	case 'add_plugin':
			$owidget->add_plugin($_POST['plugin'], $_POST['cf']);
	break;
	case 'del_plugin':
			$owidget->del_plugin($_POST['plugin']);
	break;
	case 'get_plugins':
		$pluginlist = get_childrens_plugins($jstree, 'aggregator_'.$id);
		sort(&$pluginlist);
		echo json_encode($pluginlist);
	break;
	default:
		die('No valid action submited !');
	break;
}

?>
