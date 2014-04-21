<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2013 Yves Mettier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Yves Mettier <ymettier AT free fr>
 * @copyright 2013 Yves Mettier
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

function selection_create_new($title, $node_id, $deleteafter) {
    global $db_config;
    $id = -1;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $id = $db->insert_id_before('selections', 'id', "in selection_create_new()");
        $db->prepare("INSERT INTO selections (id, title, tree_id, deleteafter, sortorder, data) VALUES (?, ?, ?, ?, ?, ?)", array('integer', 'text', 'integer', 'integer', 'integer', 'text'));
        $db->execute(array((int)$id, $title, (int)$node_id, (int)$deleteafter, (int)0, serialize(array())));
        $id = $db->insert_id_after($id, 'selections', 'id', "in selection_create_new()");
        $db->destroy();
    }
    return($id);
}

function selection_import($field, $db = null) {
    global $db_config;
    $id = -1;
    if(! $db) {$db = new _database($db_config); }
    if ($db->connect()) {
        $id = $db->insert_id_before('selections', 'id', "in selection_create_new()");
        $db->prepare("INSERT INTO selections (id, title, tree_id, deleteafter, sortorder, data) VALUES (?, ?, ?, ?, ?, ?)", array('integer', 'text', 'integer', 'integer', 'integer', 'text'));
        $db->execute(array((int)$id, $field['title'], (int)$field['tree_id'], (int)$field['deleteafter'], (int)$field['sortorder'], $field['data']));
        $id = $db->insert_id_after($id, 'selections', 'id', "in selection_create_new()");
        $db->destroy();
    }
    return($id);
}

function selection_delete($id) {
    global $db_config;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("DELETE FROM selections WHERE id = ?", array('integer'));
        $db->execute(array((int)$id));
        $db->destroy();
    }
    return;
}

function selection_delete_all_with_node_id($node_id) {
    global $db_config;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("DELETE FROM selections WHERE tree_id = ?", array('integer'));
        $db->execute(array((int)$node_id));
        $db->destroy();
    }
    return;
}

function selection_get_all_with_node_id($node_id) {
    global $db_config;
    $data = array();
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("SELECT *,CASE WHEN (sortorder = 0) THEN 999999 ELSE sortorder END AS s FROM selections WHERE tree_id=? ORDER BY s", array('integer'));
        $db->execute(array((int)$node_id));
        while($db->nextr()) {
            $v = $db->get_row('assoc');
            $data[] = $v;
        }
        $db->destroy();
    }
    return($data);
}

function selection_reorder($neworder) {
    global $db_config;
    $db = new _database($db_config);
    if ($db->connect()) {
        foreach($neworder as $id => $pos) {
            $db->prepare("UPDATE selections set sortorder=? WHERE id=?", array('integer', 'integer'));
            $db->execute(array((int)$pos,(int)$id));
        }
        $db->destroy();
    }
    return;
}

function selection_get_data($id) {
    global $db_config;
    $data = array('markup' => "");
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("SELECT data FROM selections WHERE id=?", array('integer'));
        $db->execute(array((int)$id));
        while($db->nextr()) {
            $v = $db->get_row('assoc');
            if($v['data']) {
                $data = unserialize($v['data']);
            }
        }
        $db->destroy();
    }
    return($data);
}

function selection_update_markup($id, $markup) {
    global $db_config;
    $data = array('markup' => $markup);
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("UPDATE selections SET data=? WHERE id = ?", array('text', 'integer'));
        $db->execute(array(serialize($data), (int)$id));
        $db->destroy();
    }
    return;
}

?>
