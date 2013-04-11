<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

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

$jstree = new json_tree();
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
