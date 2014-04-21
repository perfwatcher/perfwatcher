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
            if (isset($datas['container_options']['sort']) && $datas['container_options']['sort'] == 1) { $sort = 'title'; } else { $sort = 'position'; }
            $this->db->prepare(
                    "SELECT ".implode(", ", $this->fields).",cdsrc,datas FROM ".$this->table
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
                if(isset($tmp["cdsrc"])) {
                    $cdsrc = $tmp['cdsrc'];
                }
                $tmp['CdSrc'] = $cdsrc?$cdsrc:$collectd_source;
                unset($tmp['cdsrc']);
                $childrens[$tmp['pwtype'] != 'container' ? $tmp['title'] : 'aggregator_'.$tmp['id']] = $tmp;
            }
        }
        $childrens_cache[$id] = $childrens;
        return $childrens;
    }

    function set_datas($id, $data) {
        $this->db->prepare("UPDATE ".$this->table." SET datas=? WHERE view_id = ? AND id = ?", array('text', 'integer', 'integer'));
        $this->db->execute(array(serialize($data), (int)$this->view_id, (int)$id));
    }

    function get_datas($id) {
        $containers = array();
        $this->db->prepare("SELECT datas FROM ".$this->table." WHERE id = ?", array('integer'));
        $this->db->execute(array((int) $id));
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
        $id = $this->db->insert_id_before($this->table, 'id', "in class tree::_create()");
        if($parent == 1) {
            $this->view_id = $id;
        }
        $this->db->prepare("INSERT into ".$this->table." (id, view_id, parent_id, position) VALUES (?, ?, ?, ?)", array('integer', 'integer', 'integer', 'integer'));
        $this->db->execute(array((int)$id, (int)$this->view_id, (int)$parent, (int)$position) );
        return $this->db->insert_id_after($id, $this->table, 'id', "in class tree::_create()");
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
        $item = $this->_get_node($id);
        $parent_id = $item["parent_id"];

        $children = $this->_get_children($id, true);
        $this->db->prepare("DELETE FROM ".$this->table
                ." WHERE ".$this->fields["view_id"]." = ?"
                ." AND   ".$this->fields["id"]." = ?",
                array('integer', 'integer'));
        foreach($children as $child) {
            $this->db->execute(array((int)$this->view_id, (int) $child['id']));
        }
        $this->db->execute(array((int)$this->view_id, (int) $id));
        dbcompat__reorder_objects_positions($this->db, $this->table, $this->view_id, $parent_id);
        return true;
    }

    function _move($id, $ref_id, $position = 0) {
        if ($ref_id == 0) { $ref_id++; }
        # First, reorder in case there are holes in the numbering
        dbcompat__reorder_objects_positions($this->db, $this->table, $this->view_id, $ref_id);

        # Then make a hole for the new node
        $sql  = "UPDATE ".$this->table." ";
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE view_id = ? ";
        $sql .= "AND parent_id = ? ";
        $sql .= "AND position >= ? ";
        $sql .= "AND id != ?";
        $this->db->prepare($sql, array('integer', 'integer', 'integer', 'integer'));
        $this->db->execute(array($this->view_id, $ref_id,$position,$id));

        # And insert the new node at the right place
        $this->db->prepare("UPDATE ".$this->table." SET parent_id = ?, position = ? WHERE view_id = ? AND id = ?", array('integer', 'integer', 'integer', 'integer'));
        $this->db->execute(array($ref_id,$position,(int)$this->view_id, $id));

        return true;
    }

    function _reorder_positions($id) {
        dbcompat__reorder_objects_positions($this->db, $this->table, $this->view_id, $id);
        return true;
    }

}

class json_tree extends _tree_struct { 
    function __construct($view_id, $table = "tree", $fields = array(), 
            $add_fields = array(
                "title" => "title", 
                "pwtype" => "pwtype", 
                "agg_id" => "agg_id", 
                "cdsrc" => "cdsrc", 
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
        $this->db->prepare("SELECT COALESCE(MAX(position+1),0) AS position FROM tree WHERE view_id = ? AND parent_id = ?", array('integer', 'integer'));
        $this->db->execute(array((int)$this->view_id, $parent_id));
        $this->db->nextr();
        $res =  $this->db->get_row("assoc");
        return $res['position'];
    }

    function set_node($data, $id=null) {
        if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
        $sql = "UPDATE ".$this->table." SET "; 
        foreach($this->add_fields as $k => $v) {
            if(isset($data[$k])) {
                $set_sql[] = $this->fields[$v]." = ? ";
                $set_value[] = $data[$k];
                $set_type[] = 'text';
            }
        }
        $sql .= implode(", ", $set_sql);
        $sql .= " WHERE ".$this->fields["view_id"]." = ?";
        $sql .= " AND   ".$this->fields["id"]." = ?";
        $set_value[] = (int)$this->view_id;
        $set_value[] = ($id === null)?((int)$data["id"]):((int)$id);
        $set_type[] = 'integer';
        $set_type[] = 'integer';

        $this->db->prepare($sql, $set_type);
        $this->db->execute($set_value);
        return "{ \"status\" : 1 }";
    }
    function rename_node($data) { return $this->set_node($data); }

    function copy_node_fields($refid, $dstid) {
        dbcompat__copy_with_update($this->db, $this->table, $refid, $dstid, 'id', $this->add_fields);
    }

    function move_node($data) { 
        $rc = 0;
        $id = 0;
        if((int)$data["copy"]) {
            $id = parent::_create((int)$data["ref"], (int)$data["position"]);
            if(!$id) return "{ \"status\" : 0 }";
            $this->copy_node_fields($data["id"], $id);
        } else {
            $id = (int)$data["id"];
        }
        $rc = parent::_move($id, (int)$data["ref"], (int)$data["position"]);
        if(!$rc) return "{ \"status\" : 0 }";
        return "{ \"status\" : 1, \"id\" : ".$rc." }";
    }
    function remove_node($data) {
        $id = parent::_remove((int)$data["id"]);
        return "{ \"status\" : 1 }";
    }

    function generate_aggregator_id($id) {
# WARNING : this way of getting a unique id is not atomic.
# You should not use this method somewhere else than bin/aggregator or things may break.
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
        list($collectd_source, $cdsrc_is_computed, $db_cdsrc) = $this->get_node_collectd_source((int)$data["id"]);
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

    function get_node_collectd_source($id, $item=null) {
        global $collectd_source_default, $collectd_sources;

        if($item && isset($item['cdsrc']) && $item['cdsrc'] && isset($item['pwtype']) && ($item['pwtype'] == "container")) {
            return array($item['cdsrc'], 0, $item['cdsrc']);
        }
        $cdsrc = $collectd_source_default;
        $db_cdsrc = "";
        $cdsrc_is_computed = 0;
        $cached_cdsrc = "";
        $cached_host = "";
        $parent_id = $id;
        while ($parent_id != 0) {
            $this->db->prepare("SELECT parent_id,title,pwtype,cdsrc FROM ".$this->table
                    ." WHERE view_id = ?"
                    ." AND   id = ?",
                    array('integer', 'integer'));
            $this->db->execute(array((int)$this->view_id, $parent_id));
            $this->db->nextr();
            $c = $this->db->f("cdsrc");
            if($id == $parent_id) {
               // This is the cdsrc for the node #id
               $db_cdsrc = $c; 
            }
            $pwtype = $this->db->f("pwtype");
            if($pwtype == "container") {
                if(isset($c) && $c ) {
                    $cdsrc = $c;
                    break;
                }
                $cdsrc_is_computed = 1;
            } else if($pwtype == "server") {
                $cached_cdsrc = $c;
                $cached_host = $this->db->f("title");
            }
            $parent_id = $this->db->f("parent_id");
        }
        if($cdsrc == "Auto-detect") {
            if($cached_cdsrc && isset($collectd_sources[$cached_cdsrc])) {
                return array($cached_cdsrc, 1, "Auto-detect");
            }

            $collectd_source_list = array_keys($collectd_sources);
            $cdsrc = "";

            foreach ($collectd_source_list as $cs) {
                $json = json_encode(array(
                            "jsonrpc" => "2.0",
                            "method" => "pw_get_status",
                            "params" => array(
                                "timeout" => 240,
                                "server" => array($cached_host),
                                ),
                            "id" => 0)
                        );

                $ra = jsonrpc_query($cs, $json);

                if(!(isset($ra[0]) && isset($ra[1]))) { continue; }
                $data = $ra[0];
                if(isset($data[$cached_host]) && ($data[$cached_host] != "unknown")) {
                    $cdsrc = $cs;
                    $this->set_node_collectd_source($id,$cdsrc);
                    break;
                }
            }
            if($cdsrc && isset($collectd_sources[$cdsrc])) {
                return array($cdsrc, 1, "Auto-detect");
            }
            $cdsrc = $collectd_source_default;
            $cdsrc_is_computed = 1;
        }
        return array($cdsrc, $cdsrc_is_computed, $db_cdsrc);
    }

    function set_node_collectd_source($id, $cdsrc) {
        global $collectd_sources;
        $rc = 0;
        if(isset($collectd_sources[$cdsrc]) || ($cdsrc == "Auto-detect")) {
            $this->db->prepare("UPDATE ".$this->table." SET cdsrc=? WHERE id = ?", array('text', 'integer'));
            $this->db->execute(array($cdsrc, (int)$id));
        } else if($cdsrc == "") {
            $this->db->prepare("UPDATE ".$this->table." SET cdsrc=NULL WHERE id = ?", array('integer'));
            $this->db->execute(array((int)$id));
        } else {
            pw_error_log("Source '$cdsrc' is not a valid source for id=$id. No update");
            $rc = -1;
        }
        return($rc);
    }

    function set_container_nodes_autodetect_collectd_source($id) {
        global $collectd_sources, $collectd_source_default;
        $containers_id = array();
        $containers_id[] = $id;
        $servers_id = array();

        while(null !== ($cur_id = array_pop($containers_id))) {
# Check servers
            $this->db->prepare("SELECT id,title,cdsrc FROM ".$this->table
                    ." WHERE pwtype = 'server'"
                    ." AND   cdsrc NOT IN (".implode(",", array_keys($collectd_sources)).")"
                    ." AND   parent_id = ?",
                    array('integer'));
            $this->db->execute(array((int)$cur_id));
            while($this->db->nextr()) {
                $a =  $this->db->get_row("assoc");
                if( ! isset($collectd_sources[$a['cdsrc']]))
                    $servers_id[$a['title']][] = $a['id'];
            }

# Traverse containers
            $this->db->prepare("SELECT id FROM ".$this->table
                    ." WHERE pwtype = 'container'"
                    ." AND   parent_id = ?"
                    ." AND   cdsrc = 'Auto-detect'",
                    array('integer'));
            $this->db->execute(array((int)$cur_id));
            while($this->db->nextr()) {
                $a =  $this->db->get_row("assoc");
                $containers_id[] = $a['id'];
            }
        }
# Check all servers
        $local_collectd_sources = array_keys($collectd_sources);
        foreach ($local_collectd_sources as $collectd_source) {
            if(count($servers_id) <= 0) { break; }
            // Ask status for all rrd hosts
            $json = json_encode(array(
                        "jsonrpc" => "2.0",
                        "method" => "pw_get_status",
                        "params" => array(
                            "timeout" => 240,
                            "server" => array_keys($servers_id),
                            ),
                        "id" => 0)
                    );

            $ra = jsonrpc_query($collectd_source, $json);

            if(!(isset($ra[0]) && isset($ra[1]))) { continue; }
            $data = $ra[0];

            if($data) {
                $server_source[$collectd_source] = array();
                foreach ($data as $h => $r) {
                    if($r != "unknown") {
                        $server_source[$collectd_source] = array_merge($server_source[$collectd_source], $servers_id[$h]);
                        unset($servers_id[$h]);
                    }
                }
            }
        }
# Update all servers with identified sources
        if(isset($server_source)) {
            foreach (array_keys($server_source) as $collectd_source) {
                if(!isset($server_source[$collectd_source])) { next; }
                $this->db->prepare("UPDATE ".$this->table." SET cdsrc=? WHERE id IN (".implode(",", $server_source[$collectd_source]).")", array('text'));
                $this->db->execute(array($collectd_source));
            }
        }
        if(count($servers_id) > 0) {
            $this->db->prepare("UPDATE ".$this->table." SET cdsrc=? WHERE id IN (".implode(",", array_keys($servers_id)).")", array('text'));
            $this->db->execute(array($collectd_source_default));
        }
    }

    function _create_default() {
    }

    function create_tree(&$list, $parent) {
        $tree = array();
        foreach ($parent as $k=>$l){
            if(isset($list[$l['id']])){
                $l['children'] = $this->create_tree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        } 
        return $tree;
    }

    function new_tree($list, $root_id) {
        $new = array();
        foreach ($list as $a){
            $new[$a['parent_id']][] = $a;
        }
        $tree = $this->create_tree($new, $new[$root_id]); // changed
        return($tree);
    }

    function import_node($parent_id, $node, $is_root) {
# Checks before import
        if(!isset($node['title'])) { return(array(false, $parent_id, "Missing 'title'", array())); }
        if(!isset($node['pwtype'])) { return(array(false, $parent_id, "pwtype", array())); }
        switch($node['pwtype']) {
            case "container": break;
            case "server": break;
            case "selection": break;
            default : return(array(false, $parent_id, "pwtype '".$node['pwtype']."' not known", array()));
        }

# Check the position
        $position = 0;
        if($is_root) {
            $position = (int) $this->max_pos($parent_id);
        } else if(isset($node['position'])) {
            $position = (int) $node['position'];
        }
# Start the import
        $id = parent::_create((int)$parent_id, (int) $position);
        if( ! $id ) {
            return (array(false, $parent_id, "Could not import '".$node['title']."'", array()));
        }
        $data = array('id' => $id, 'title' => $node['title'], 'pwtype' => $node['pwtype']);
        foreach(array('agg_id', 'datas', 'cdsrc') as $k) {
            if(isset($node[$k])) {
                $data[$k] = $node[$k];
            }
        }
        $this->set_node($data);
        $id_mapping = array();
        if(isset($node['id'])) {
            $id_mapping[$node['id']] = $id;
        }
# Import the children if any
        if(isset($node['children']) && $node['children']) {
            foreach ($node['children'] as $n) {
                list($rc, $rid, $rmsg, $rmap) = $this->import_node($id, $n, 0);
                $id_mapping = $id_mapping + $rmap;
                if(! $rc) { return(array($rc,$rid, $rmsg, $id_mapping)); }
            }
            dbcompat__reorder_objects_positions($this->db, $this->table, $this->view_id, $id);
        }
        return(array(true, $id, "OK", $id_mapping ));
    }

    function import_selections($selections, $id_mapping) {
        foreach($selections as $sel) {
            if(!isset($sel['title'])) { return(array(false, 0, "No title for a selection")); }
            if(!isset($sel['tree_id'])) { return(array(false, 0, "No tree_id for selection with title '".$sel['title']."'")); }
            if(!isset($sel['deleteafter'])) { return(array(false, 0, "No deleteafter for selection with title '".$sel['title']."'")); }
            if(!isset($sel['sortorder'])) { return(array(false, 0, "No sortorder for selection with title '".$sel['title']."'")); }
            if(!isset($sel['data'])) { return(array(false, 0, "No data for selection with title '".$sel['title']."'")); }
        }
        foreach($selections as $sel) {
            $sel['tree_id'] = $id_mapping[$sel['tree_id']];
            selection_import($sel, $this->db);
        }
        return(array(true, 0, "OK" ));
    }

    function tree_import_from_file($args) {
        $tmpname = $_FILES['tree_import_json']['tmp_name'];
        $json = file_get_contents($tmpname);
        list($rc, $id, $str) = $this->_tree_import($args['id'], $json);

        return(json_encode(array('status' => ($rc?1:0), 'errorstring' => ($rc?"":$str))));
    }

    function tree_import($args) {
        $field = array();
        if(!isset($args['json'])) { return(json_encode(array('status' => 0))); }


        list($rc, $id, $str) = $this->_tree_import($args['id'], $args['json']);

        return(json_encode(array('status' => ($rc?1:0), 'errorstring' => ($rc?"":$str))));
    }

    function _tree_import($id, $json) {
        $imported_tree = json_decode($json, true);
        if((!isset($imported_tree[0])) || (!isset($imported_tree[0]['nodes'])) || (!isset($imported_tree[0]['nodes'][0])) ) {
            return(array(false, $id, "No nodes to import (or JSON syntax error)"));
        }
        if((!isset($imported_tree[0])) || (!isset($imported_tree[0]['version'])) ) {
            return(array(false, $id, "Version not specified"));
        }
        if($imported_tree[0]['version'] != "1.0") {
            return(array(false, $id, "Version '".$imported_tree[0]['version']."' not supported"));
        }
        if((!isset($imported_tree[0])) || (!isset($imported_tree[0]['dbschema_version'])) ) {
            return(array(false, $id, "DB Schema Version not specified"));
        }
        list($rc, $rid, $rmsg, $id_mapping) = $this->import_node($id, $imported_tree[0]['nodes'][0], 1);
        if(!$rc) {
            return(array(false, $rid, $rmsg));
        }
        if((isset($imported_tree[0])) && (isset($imported_tree[0]['selections'])) ) {
            list($rc, $rid, $rmsg) = $this->import_selections($imported_tree[0]['selections'], $id_mapping);
            if(!$rc) {
                return(array(false, $rid, $rmsg));
            }
        }
        return(array(true, 0, "OK" ));
    }

    function _tree_export_generic($args) {
        $field = array();
        if(isset($args['options']['position']) && ($args['options']['position'] == 'yes')) { $field[] = "position"; }
        if(isset($args['options']['datas']) && ($args['options']['datas'] == 'yes')) { $field[] = "datas"; }
        if(isset($args['options']['cdsrc']) && ($args['options']['cdsrc'] == 'yes')) { $field[] = "cdsrc"; }

        $options = array();
        if(count($field)) {
            $options['fields'] = implode(',',$field);
        }
        if(isset($args['pretty_print']) && $args['pretty_print']) {
            $options['pretty_print'] = 1;
        }
        $result = $this->_tree_export($args['id'], $options);
        return($result);
    }

    function tree_export_as_file($args) {
        $result = $this->_tree_export_generic($args);
        return($result);
    }

    function tree_export($args) {
        $result = $this->_tree_export_generic($args);
        return(json_encode(array('str' => $result)));
    }

    function _tree_export($id, $args) {
        /* args keys :
            'fields' : include fields ("all" or some of "position", "datas", "cdsrc" from the db definition)
            'pretty_print' : true/false
         */
        $export_version = "1.0";
        $fields = array("id", "parent_id", "title", "pwtype", "agg_id");

        $containers_id = array();
        $id_list = array();

        $nodes = array();
        $selections = array();

        if(isset($args['fields']) && ($args['fields'] != "all")) {
            $a = preg_split("/[\s,]+/", $args['fields'], PREG_SPLIT_NO_EMPTY);
            foreach ($a as $f) {
                if(!in_array($f, $fields)) {
                    $fields[] = $f;
                }
            }
        }
# Get the root node info
        $this->db->prepare("SELECT ".implode(", ", $fields)." FROM ".$this->table
                ." WHERE id = ?",
                array('integer'));
        $this->db->execute(array((int)$id));
        while($this->db->nextr()) {
            $a =  $this->db->get_row("assoc");
            $id_list[$a['id']] = 1;
            $root_id = $a['parent_id'];
            unset($a['view_id']);
            if(! $a['agg_id']) {
                unset($a['agg_id']);
            }
            $nodes[$a['id']] = $a;
            if($a['pwtype'] == 'container') {
                $containers_id[] = $a['id'];
            }
        }
        while(null !== ($cur_id = array_pop($containers_id))) {
# Traverse containers
            $this->db->prepare("SELECT ".implode(", ", $fields)." FROM ".$this->table
                    ." WHERE parent_id = ?",
                    array('integer'));
            $this->db->execute(array((int)$cur_id));
            while($this->db->nextr()) {
                $a =  $this->db->get_row("assoc");
                $id_list[$a['id']] = 1;
                unset($a['view_id']);
                $nodes[$a['id']] = $a;
                if($a['pwtype'] == 'container') {
                    $containers_id[] = $a['id'];
                }
            }
        }

# Create the list of nodes
        $tree = $this->new_tree($nodes, $root_id);

# Create the list of tabs (for selections, servers or folders)
        $this->db->query("SELECT * FROM selections "
                ." WHERE tree_id IN (".implode(',',array_keys($id_list)).")");
        while($this->db->nextr()) {
            $a =  $this->db->get_row("assoc");
            $selections[] = $a;
        }
# Encode the result
        $array_result = array(
                "nodes" => $tree, 
                "selections" => $selections,
                "version" => $export_version,
                "dbschema_version" => $this->db->get_db_schema(),
            );
        if(isset($args['pretty_print']) && $args['pretty_print']) {
            $json_result = json_format($array_result);
        } else {
            $json_result = json_encode($array_result);
        }
        return($json_result);
    }


    function _drop() {
        $this->db->query("TRUNCATE ".$this->table);
    }
}

?>
