<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 

require 'lib/class.folder_filling_regex.php';

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
$owidget = new folder_filling_regex($res);
switch($_POST['action']) {
    case 'test':
        echo nl2br($owidget->test($_POST['regex']));
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
