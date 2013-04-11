<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

$jstree = new json_tree($view_id);
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case "sort":
            if (isset($_POST['sort']) && is_numeric($_POST['sort']) && isset($_POST['id']) && is_numeric($_POST['id'])) {
                $datas = $jstree->get_datas($_POST['id']);
                $datas['sort'] = $_POST['sort'];
                $jstree->set_datas($_POST['id'], $datas);
            }
        break;

    }
}

?>
