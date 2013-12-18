<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 

$id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

$jstree = new json_tree($view_id);
$res = $jstree->_get_node($id);
$hosts = array();
$cdsrc = $jstree->get_node_collectd_source($id);
$data = $jstree->_get_children($id, true, "", "", $cdsrc);
foreach($data as $host) {
    if ($host['pwtype'] == 'server') { $hosts[] = array('title' => $host['title'], 'CdSrc' => $host['CdSrc']); }
}
echo json_encode($hosts);

?>
