<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

require 'lib/class.folder_filling_manual.php';

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);

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
