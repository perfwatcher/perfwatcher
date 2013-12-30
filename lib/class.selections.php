<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Common functions
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Monitoring
 * @author    Yves Mettier <ymettier@free.fr>
 * @copyright 2013 Yves Mettier
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */

function selection_create_new($title, $node_id, $deleteafter) {
    global $db_config;
    $id = -1;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("INSERT INTO selections (title, tree_id, deleteafter) VALUES (?, ?, ?)", array('text', 'integer', 'integer'));
        $db->execute(array($title, (int)$node_id, (int)$deleteafter));
        $id = $db->insert_id('selections', 'id');
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
        $db->prepare("SELECT * FROM selections WHERE tree_id=?", array('integer'));
        $db->execute(array((int)$node_id));
        while($db->nextr()) {
            $v = $db->get_row('assoc');
            $data[] = $v;
        }
        $db->destroy();
    }
    return($data);
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
