<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2011 Cyril Feraudet
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
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

class _tree_struct {
    // Structure table and fields
    protected $table	= "";
    protected $view_id	= 0;
    protected $fields	= array(
            "id"		=> false,
            "view_id"	=> false,
            "parent_id"	=> false,
            "position"	=> false,
            );

    // Constructor
    function __construct($view_id, $table = "tree", $fields = array()) {
        global $db_config;
        $this->table = $table;
        $this->view_id = $view_id;
        if(!count($fields)) {
            foreach($this->fields as $k => &$v) { $v = $k; }
        }
        else {
            foreach($fields as $key => $field) {
                switch($key) {
                    case "id":
                    case "parent_id":
                    case "position":
                    $this->fields[$key] = $field;
                    break;
                }
            }
        }
        // Database
        $this->db = new _database($db_config);
    }

    function _get_node($id) {
        $this->db->prepare(
                "SELECT ".implode(", ", $this->fields)." FROM ".$this->table
                ." WHERE ".$this->fields["view_id"]." = ?"
                ." AND   ".$this->fields["id"]." = ?",
                array('integer', 'integer')
                );
        $this->db->execute(array((int)$this->view_id, (int)$id));
        $this->db->nextr();
        $ret = $this->db->nf() === 0 ? false : $this->db->get_row("assoc");
        $this->db->free();
        return $ret;
    }
    function _get_children($id, $recursive = false, $path = "", $separator = " -> ", $collectd_source = "") {
        global $childrens_cache;
        if(is_array($childrens_cache) && isset($childrens_cache[$id.($recursive ? 'recursive' : 'notrecursive')])) {
            return $childrens_cache[$id];
        }
        $childrens = array();
        if($recursive) {
            $childrens = $this->_get_children($id, false, $path, $separator, $collectd_source);
            foreach($childrens as $cid => $cdata) {
                if ( $cdata['pwtype'] == 'container') {			
                    foreach($this->_get_children($cdata['id'], true, $cdata['_path_'], $separator, $cdata['CdSrc']) as $cid2 => $cdata2) {
                        $childrens[$cdata2['pwtype'] != 'container' ? $cdata2['title'] : 'aggregator_'.$cdata2['id']] = $cdata2;
                    }
                }
            }
        } else {
            $datas = $this->get_datas($id);
            if (isset($datas['sort']) && $datas['sort'] == 1) { $sort = 'title'; } else { $sort = 'position'; }
            $this->db->prepare(
                    "SELECT ".implode(", ", $this->fields).",datas FROM ".$this->table
                    ." WHERE ".$this->fields["view_id"]." = ?"
                    ." AND   ".$this->fields["parent_id"]." = ?"
                    ." ORDER BY ".$this->fields[$sort]." ASC",
                    array('integer', 'integer')
                    );
            $this->db->execute(array((int)$this->view_id, (int)$id));
            while($this->db->nextr()) {
                $tmp = $this->db->get_row("assoc");
                $tmp["_path_"] = $path.$separator.$tmp['title'];
                $cdsrc = "";
                if(isset($tmp["datas"])) {
                    $d = unserialize($tmp["datas"]);
                    if(isset($d['CdSrc'])) {
                        $cdsrc = $d['CdSrc'];
                    }
                    unset($tmp["datas"]);
                }
                $tmp['CdSrc'] = $cdsrc?$cdsrc:$collectd_source;
                $childrens[$tmp['pwtype'] != 'container' ? $tmp['title'] : 'aggregator_'.$tmp['id']] = $tmp;
            }
        }
        $childrens_cache[$id] = $childrens;
        return $childrens;
    }

    function set_datas($id, $data) {
# TODO: sql/id
        $this->db->prepare("UPDATE ".$this->table." SET datas=? WHERE view_id = ? AND id = ?", array('text', 'integer', 'integer'));
        $this->db->execute(array(serialize($data), (int)$this->view_id, (int)$id));
    }

    function get_datas($id) {
        $containers = array();
        $this->db->prepare("SELECT datas FROM ".$this->table." WHERE view_id = ? AND id = ?", array('integer', 'integer'));
        $this->db->execute(array((int)$this->view_id, (int) $id));
        $this->db->nextr();
        $datas = $this->db->get_row("assoc");
        if(!$ret = unserialize($datas["datas"])) { $this->db->free(); return array(); }
        $this->db->free();
        return $ret;
    }

    function get_containers() {
        $containers = array();
        $this->db->prepare("SELECT ".implode(", ", $this->fields)." FROM ".$this->table." WHERE pwtype = 'container' and view_id = ?", array('integer'));
        $this->db->execute(array((int)$this->view_id));
        while($this->db->nextr()) $containers[$this->db->f($this->fields["id"])] = $this->db->get_row("assoc");
        return $containers;
    }

    function _create($parent, $position) {
# TODO: sql/id
        $this->db->prepare("INSERT into ".$this->table." (view_id, parent_id, position) VALUES (?, ?, ?)", array('integer', 'integer', 'integer'));
        $this->db->execute(array((int)$this->view_id, (int)$parent, (int)$position) );
        return $this->db->insert_id($this->table, 'id');
    }

    function del_node($title) {
        $id = false;
        while (true) {
            $this->db->setLimit(1);
            $this->db->prepare("SELECT id FROM ".$this->table
                    ." WHERE ".$this->fields["view_id"]."= ?"
                    ." AND   ".$this->fields["title"]."= ?",
                    array('integer', 'text')
            );
            $this->db->execute(array((int)$this->view_id, $title));
            while($this->db->nextr()) $id = $this->db->f($this->fields["id"]);
            if (is_numeric($id)) {
                $this->_remove($id);
                $id = false;
            } else { return; }
        }
    }

    function _remove($id) {
        if((int)$id === 1) { return false; }
        $children = $this->_get_children($id, true);
        $this->db->prepare("DELETE FROM ".$this->table
                ." WHERE ".$this->fields["view_id"]." = ?"
                ." AND   ".$this->fields["id"]." = ?",
                array('integer', 'integer'));
        foreach($children as $child) {
            $this->db->execute(array((int)$this->view_id, (int) $child['id']));
        }
        $this->db->execute(array((int)$this->view_id, (int) $id));
        return true;
    }

    function _move($id, $ref_id, $position = 0, $is_copy = false) {
        if ($ref_id == 0) { $ref_id++; }
        $sql  = "UPDATE ".$this->table." ";
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE view_id = ? ";
        $sql .= "AND parent_id = ? ";
        $sql .= "AND position >= ? ";
        $sql .= "AND id != ?";
        $this->db->prepare($sql, array('integer', 'integer', 'integer', 'integer'));
        $this->db->execute(array($this->view_id, $ref_id,$position,$id));

        $this->db->prepare("UPDATE ".$this->table." SET parent_id = ?, position = ? WHERE view_id = ? AND id = ?", array('integer', 'integer', 'integer', 'integer'));
        $this->db->execute(array($ref_id,$position,(int)$this->view_id, $id));

# TODO: sql/compat
        $this->db->query("SET @a=-1");
        $this->db->prepare("UPDATE ".$this->table." SET position = @a:=@a+1 WHERE view_id = ? AND parent_id = ? ORDER BY position", array('integer', 'integer'));
        $this->db->execute(array($this->view_id, $ref_id));
        return true;
    }

}

class json_tree extends _tree_struct { 
    function __construct($view_id, $table = "tree", $fields = array(), 
            $add_fields = array(
                "title" => "title", 
                "pwtype" => "pwtype", 
                "agg_id" => "agg_id", 
                "datas" => "datas"
                )) {

        parent::__construct($view_id, $table, $fields);
        $this->fields = array_merge($this->fields, $add_fields);
        $this->add_fields = $add_fields;
    }

    function create_node($data) {
        $id = parent::_create((int)$data[$this->fields["id"]], (int)$data[$this->fields["position"]]);
        if($id) {
            $data["id"] = $id;
            $this->set_node($data);
            return  "{ \"status\" : 1, \"id\" : ".(int)$id." }";
        }
        return "{ \"status\" : 0 }";
    }

    function add_node($parent_id, $title) {
        $id = parent::_create((int)$parent_id, (int) $this->max_pos($parent_id));
        if($id) {
            $data = array('id' => $id, 'title' => $title, 'pwtype' => 'server');
            $this->set_node($data);
            return  true;
        }
        return false;
    }

    function add_selection($parent_id, $title) {
        $id = parent::_create((int)$parent_id, (int) $this->max_pos($parent_id));
        if($id) {
            $data = array('id' => $id, 'title' => $title, 'pwtype' => 'selection');
            $this->set_node($data);
            return  true;
        }
        return false;
    }

    function add_folder($parent_id, $title) {
        $id = parent::_create((int)$parent_id, (int) $this->max_pos($parent_id));
        if($id) {
            $data = array('id' => $id, 'title' => $title, 'pwtype' => 'container');
            $this->set_node($data);
            return  true;
        }
        return false;
    }

    function max_pos($parent_id) {
# TODO: sql/compat
        $this->db->prepare("SELECT IFNULL(MAX(position+1),0) AS position FROM tree WHERE view_id = ? AND parent_id = ?", array('integer', 'integer'));
        $this->db->execute(array((int)$this->view_id, $parent_id));
        $this->db->nextr();
        $res =  $this->db->get_row("assoc");
        return $res['position'];
    }

    function set_node($data) {
        if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
# TODO: sql/id
# Note : is << SET ".$this->fields["id"]." = ".$this->fields["id"]." " >> useful ?
# Note : cannot we do it another way ?
        $sql = "UPDATE ".$this->table." SET ".$this->fields["id"]." = ".$this->fields["id"]." "; 
        foreach($this->add_fields as $k => $v) {
            if(isset($data[$k])) {
                $sql .= ", ".$this->fields[$v]." = ? ";
                $set_value[] = $data[$k];
                $set_type[] = 'text';
            }
        }
        $sql .= " WHERE ".$this->fields["view_id"]." = ?";
        $sql .= " AND   ".$this->fields["id"]." = ?";
        $set_value[] = (int)$this->view_id;
        $set_value[] = (int)$data["id"];
        $set_type[] = 'integer';
        $set_type[] = 'integer';

        $this->db->prepare($sql, $set_type);
        $this->db->execute($set_value);
        return "{ \"status\" : 1 }";
    }
    function rename_node($data) { return $this->set_node($data); }

    function move_node($data) { 
        $id = parent::_move((int)$data["id"], (int)$data["ref"], (int)$data["position"], (int)$data["copy"]);
        if(!$id) return "{ \"status\" : 0 }";
        if((int)$data["copy"] && count($this->add_fields)) {
            $ids	= array_keys($this->_get_children($id, true));
            $data	= $this->_get_children((int)$data["id"], true);

            $i = 0;
            foreach($data as $dk => $dv) {
# TODO: sql/id
# Note : is << SET ".$this->fields["id"]." = ".$this->fields["id"]." " >> useful ?
# Note : cannot we do it another way ?
                $sql = "UPDATE ".$this->table." SET ".$this->fields["id"]." = ".$this->fields["id"]." "; 
                foreach($this->add_fields as $k => $v) {
                    if(isset($dv[$k])) {
                        $sql .= ", ".$this->fields[$v]." = ? ";
                        $set_value[] = $dv[$k];
                        $set_type[] = 'text';
                    }
                }
                $sql .= " WHERE ".$this->fields["view_id"]." = ?";
                $sql .= " AND   ".$this->fields["id"]." = ?";
                $set_value[] = (int)$this->view_id;
                $set_value[] = (int)$ids[$i];
                $set_type[] = 'integer';
                $set_type[] = 'integer';

                $this->db->prepare($sql, $set_type);
                $this->db->execute($set_value);
                $i++;
            }
        }
        return "{ \"status\" : 1, \"id\" : ".$id." }";
    }
    function remove_node($data) {
        $id = parent::_remove((int)$data["id"]);
        return "{ \"status\" : 1 }";
    }

    function generate_aggregator_id($id) {
# WARNING : this way of getting a unique id is not atomic.
# You should not use this method somewhere else than bin/aggregator or things may break.
# TODO: sql/compat
        $this->db->query("SELECT agg_id FROM ".$this->table." WHERE agg_id < (5+(select count(distinct agg_id) from ".$this->table."))  order by agg_id asc");
        $agg_id = 0;
        while($this->db->nextr()) {
            $a =  $this->db->get_row("assoc");
            if($agg_id == 0) $agg_id = $a['agg_id'];
            if($agg_id == $a['agg_id']) {
                $agg_id++;
            }
            if($agg_id < $a['agg_id']) { break; }
        }
        $this->db->prepare("UPDATE ".$this->table." SET agg_id=? WHERE id = ?", array('integer', 'integer'));
        $this->db->execute(array((int)$agg_id, (int)$id));
        return $agg_id;
    }

    function get_name_from_node_id($arrayid) {

        $this->db->query("SELECT title, id FROM ".$this->table." WHERE id IN (".implode(",", $arrayid).")");
        while($this->db->nextr()) {
            $results[] =  $this->db->get_row("assoc");
        }
        return $results;
    }

    function get_jstree_type($item) {
        $type = "default";
        switch($item[$this->fields["pwtype"]]) {
            case "server" : $type = "default"; break;
            case "selection" : $type = "selection"; break;
            case "container" : $type = "folder"; break;
        }
        if(($item[$this->fields["pwtype"]] == "container") && ($item[$this->fields["parent_id"]] == 1)) {
            $type = "drive";
        }
        return($type);
    }

    function get_children($data) {
        global $collectd_source_default;
        $tmp = $this->_get_children((int)$data["id"]);
        if((int)$data["id"] === 1 && count($tmp) === 0) {
            return json_encode(
                    array(
                        "attr" => array(
                            "id" => "node_1",
                            "rel" => "drive",
                            "pwtype" => "container",
                            "CdSrc" => $collectd_source_default
                            ),
                        "data" => "INSERT A NEW ROOT AND RELOAD THE TREE",
                        "state" => ""
                        )
                    );
        }
        $collectd_source = $this->get_node_collectd_source((int)$data["id"]);
        $result = array();
        //if((int)$data["id"] === 0) return json_encode($result);
        foreach($tmp as $k => $v) {
            $tmp2 = $this->_get_children((int)$v["id"], /* $recursive = */ false, /* $path = */ "", /* $separator = */ " -> ", $collectd_source);
# compute type of item in jstree
            $type = $this->get_jstree_type($v);
# compute state of item in jstree
            $state = "";
            if(($v[$this->fields["pwtype"]] == "container") && (count($tmp2) !== 0)) { $state = "closed"; }
            $result[] = array(
                    "attr" => array(
                        "id" => "node_".$v['id'], 
                        "rel" => $type,
                        "CdSrc" => (isset($v['CdSrc']) && $v['CdSrc']) ? $v['CdSrc'] : $collectd_source
                        ),
                    "data" => $v[$this->fields["title"]],
                    "state" => $state,
                    );
        }
        if (count($result) == 0) {
            $item = $this->_get_node($data["id"]);
            $type = $this->get_jstree_type($item);
            $result[] = array(
                    "attr" => array(
                        "id" => "node_".$item['id'],
                        "rel" => $type,
                        "CdSrc" => (isset($v['CdSrc']) && $v['CdSrc']) ? $v['CdSrc'] : $collectd_source,
                        ),
                    "data" => $item["title"], 
                    "state" => ""
                    );
        }
        return json_encode($result);
    }

    function searchfield($data) {
        $result = array();
        $this->db->setLimit(30);
        $this->db->prepare("SELECT DISTINCT(".$this->fields["title"].") FROM ".$this->table
                ." WHERE ".$this->fields["view_id"]." = ?"
                ." AND   ".$this->fields["title"]." LIKE ?",
                array('integer', 'text'));
        $this->db->execute(array((int)$this->view_id, "%$data%"));
        if($this->db->nf() === 0) return "[]";
        while($this->db->nextr()) {
            $result[] = array('id' => $this->db->f("title"), 'label' => $this->db->f("title"), 'value' => $this->db->f("title"));
        }
        return json_encode($result);
    }

    function search($data) {
        $parents = array();
        $this->db->prepare("SELECT ".$this->fields["id"]." FROM ".$this->table
                ." WHERE ".$this->fields["view_id"]." = ?"
                ." AND   ".$this->fields["title"]." LIKE ?",
                array('integer', 'text'));
        $this->db->execute(array((int)$this->view_id, "%".$data["search_str"]."%"));
        if($this->db->nf() === 0) return "[]";
        while($this->db->nextr()) {
            $parents = array_merge($parents, $this->get_parents($this->db->f($this->fields["id"])));
        }
        $result = array();
        foreach( $parents as $id) { $result[] = "#node_".$id; }
        return json_encode($result);
    }

    function get_parents($parent_id) {
        $ids = array();
        while ($parent_id != 0) {
            $this->db->prepare("SELECT parent_id FROM ".$this->table
                    ." WHERE view_id = ?"
                    ." AND   id = ?",
                    array('integer', 'integer'));
            $this->db->execute(array((int)$this->view_id, $parent_id));
            $this->db->nextr();
            $parent_id = $this->db->f("parent_id");
            $ids[] = $parent_id;
        }
        return $ids;
    }

    function get_node_collectd_source($parent_id) {
        global $collectd_source_default;
        $cdsrc = $collectd_source_default;
        while ($parent_id != 0) {
            $this->db->prepare("SELECT parent_id,datas FROM ".$this->table
                    ." WHERE view_id = ?"
                    ." AND   id = ?",
                    array('integer', 'integer'));
            $this->db->execute(array((int)$this->view_id, $parent_id));
            $this->db->nextr();
            $parent_id = $this->db->f("parent_id");
            $datas = $this->db->f("datas");
            if(!$ret = unserialize($datas)) { continue; }
            if(isset($ret['CdSrc']) ) {
                $cdsrc = $ret['CdSrc'];
                break;
            }
        }
        return $cdsrc;
    }

    function _create_default() {
    }

    function _drop() {
        $this->db->query("TRUNCATE ".$this->table);
    }
}

?>
