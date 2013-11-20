<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

require 'lib/class.folder_options.php';

global $collectd_sources;

if (!isset($_GET['action']) and !isset($_POST['action'])) {
    die('Error : POST or GET action missing !!');
}

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

$action_need_jstree = 0;
switch ($action) {
    case "get_config_list":
        echo json_encode(array_keys($collectd_sources));
        break;

    default:
        $action_need_jstree = 1;
}

if($action_need_jstree) {
    $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
    $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

    $jstree = new json_tree($view_id);
    $res = $jstree->_get_node($id);
    $owidget = new folder_options($res);
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {

            case "save_sort":
                $sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_POST['sort']) ? $_POST['sort'] : '');
                if ($owidget->save_sort($sort)) {
                    die('Sort method saved.');
                }
                break;

            case "save_cdsrc":
                $src = isset($_GET['src']) ? $_GET['src'] : (isset($_POST['src']) ? $_POST['src'] : '');
                if ($owidget->save_cdsrc($src)) {
                    die('Conditions saved. You can now refresh the tree');
                }
                break;

            default:
                die('No valid action submited !');
                break;

        }
    }
}
?>
