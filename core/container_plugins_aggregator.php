<?php

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
$res = $jstree->_get_node($id);
$childrens_plugins = array_keys(get_childrens_plugins($jstree, 'aggregator_'.$res['id']));
$tpl->assign('childrens_plugins', $childrens_plugins);
$tpl->assign('dbdata', $res);

?>
