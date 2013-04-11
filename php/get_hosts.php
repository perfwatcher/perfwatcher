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
$res = $jstree->_get_node($id);
$hosts = array();
$data = $jstree->_get_children($id, true);
foreach($data as $host) {
    if ($host['type'] == 'default') { $hosts[] = $host['title']; }
}
echo json_encode($hosts);

?>
