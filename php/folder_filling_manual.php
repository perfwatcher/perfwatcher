<?php

require 'lib/class.folder_filling_manual.php';

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
$owidget = new folder_filling_manual($res);
switch($_POST['action']) {
	case 'save':
			if ($owidget->save($_POST['list'])) {
				die('Server list saved. Wait a minute then refresh the tree');
			}
	break;
	default:
		die('No valid action submited !');
	break;
}

?>
