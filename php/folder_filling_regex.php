<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

require 'lib/class.folder_filling_regex.php';

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

if (!isset($_POST['action'])) { die('No action submited !'); }

$jstree = new json_tree($view_id);
$res = $jstree->_get_node($id);
$owidget = new folder_filling_regex($res);
switch($_POST['action']) {
    case 'test':
        echo $owidget->test($_POST['regex']);
//        echo nl2br($owidget->test($_POST['regex']));
        break;
    case 'save':
        if ($owidget->save($_POST['regex'])) {
            die('Regex saved. Wait a minute then refresh the tree');
        }
        break;
    default:
        die('No valid action submited !');
        break;
}

?>
