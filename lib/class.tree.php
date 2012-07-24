<?php
/**
 * Tree lib adapted from JStree http://www.jstree.com/
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
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */

class _tree_struct {
	// Structure table and fields
	protected $table	= "";
	protected $fields	= array(
			"id"		=> false,
			"parent_id"	=> false,
			"position"	=> false,
		);

	// Constructor
	function __construct($table = "tree", $fields = array()) {
		$this->table = $table;
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
		$this->db = new _database;
	}

	function _get_node($id) {
		$this->db->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["id"]."` = ".(int) $id);
		$this->db->nextr();
		return $this->db->nf() === 0 ? false : $this->db->get_row("assoc");
	}
	function _get_children($id, $recursive = false) {
		global $childrens_cache;
		if(is_array($childrens_cache) && isset($childrens_cache[$id.($recursive ? 'recursive' : 'notrecursive')])) {
			return $childrens_cache[$id];
		}
		$childrens = array();
		if($recursive) {
			$childrens = $this->_get_children($id);
			foreach($childrens as $cid => $cdata) {
				if ( $cdata['type'] != 'default') {			
					foreach($this->_get_children($cdata['id'], true) as $cid2 => $cdata2) {
						$childrens[$cdata2['type'] == 'default' ? $cdata2['title'] : 'aggregator_'.$cdata2['id']] = $cdata2;
					}
				}
			}
		} else {
			$datas = $this->get_datas($id);
			if (isset($datas['sort']) && $datas['sort'] == 1) { $sort = 'title'; } else { $sort = 'position'; }
			$this->db->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE `".$this->fields["parent_id"]."` = ".(int) $id." ORDER BY `".$this->fields[$sort]."` ASC");
			while($this->db->nextr()) {
				$tmp = $this->db->get_row("assoc");
				$childrens[$tmp['type'] == 'default' ? $tmp['title'] : 'aggregator_'.$tmp['id']] = $tmp;
			}
		}
		$childrens_cache[$id] = $childrens;
		return $childrens;
	}

	function get_children_count($id) {
		$nbhosts = 0;
		$nbcontainer = 0;
		$childrens = $this->_get_children($id, true);
		foreach($childrens as $cid => $cdata) {
			if ($cdata['type'] == 'default') {
				$nbhosts++;
			} else {
				$nbcontainer++;
			}
		}
		return array($nbhosts, $nbcontainer);
	}

	function get_nodechildren_id($id) {
		$nodes = array();
		$childrens = $this->_get_children($id, true);
		foreach($childrens as $cid => $cdata) {
			if ($cdata['type'] == 'default') {
				$nodes[] = $cdata['id'];
			}
		}
		return $nodes;
	}

	function set_datas($id, $data) {
		$this->db->query("UPDATE ".$this->table." SET datas='".mysql_real_escape_string(serialize($data))."' WHERE id = $id");
	}

	function get_datas($id) {
		$containers = array();
		$this->db->query("SELECT datas FROM `".$this->table."` WHERE id = $id");
		$this->db->nextr();
        $datas = $this->db->get_row("assoc");
		if(!$ret = unserialize($datas["datas"])) { return array(); }
		if(isset($ret['tabs']) && count($ret['tabs']) > 0) {
			//migrate from Alpha
			foreach($ret['tabs'] as $tabid => $tabdatas) {
				if (isset($tabdatas['selected_graph']) && is_array($tabdatas['selected_graph'])) {
					foreach($tabdatas['selected_graph'] as $pluginid => $plugindatas) {
						if(is_array($plugindatas)) { continue; }
						$ret['tabs'][$tabid]['selected_graph'][$pluginid] = split('\|', $plugindatas,4);
					}
				}
			}
		}
        return $ret;
	}

	function get_containers() {
		$containers = array();
		$this->db->query("SELECT `".implode("` , `", $this->fields)."` FROM `".$this->table."` WHERE type = 'folder' or type = 'drive'");
		while($this->db->nextr()) $containers[$this->db->f($this->fields["id"])] = $this->db->get_row("assoc");
		return $containers;
	}

	function _create($parent, $position) {
		$this->db->query("INSERT into `".$this->table."` (`parent_id`, `position`, `type`) VALUES ($parent,  $position,  'default')");
		return $this->db->insert_id();
	}

	function del_node($title) {
		$id = false;
		while (true) {
			$this->db->query("SELECT id FROM `".$this->table."` WHERE ".$this->fields["title"]."= '$title' LIMIT 1");
			while($this->db->nextr()) $id = $this->db->f($this->fields["id"]);
			if (is_numeric($id)) {
				$this->_remove($id);
				$id = false;
			} else { return; }
		}
	}

	function _remove($id) {
		if((int)$id === 1) { return false; }
		$childrens = $this->_get_children($id, true);
		foreach($childrens as $children) {
			$this->db->query("DELETE FROM `".$this->table."` " . 
				"WHERE `".$this->fields["id"]."` = ".$children['id']);
		}
		$this->db->query("DELETE FROM `".$this->table."` " . 
			"WHERE `".$this->fields["id"]."` = ".$id);
		return true;
	}

	function _move($id, $ref_id, $position = 0, $is_copy = false) {
		if ($ref_id == 0) { $ref_id++; }
		$sql  = "UPDATE `".$this->table."` ";
		$sql .= "SET position = position + 1 ";
		$sql .= "WHERE parent_id = $ref_id ";
		$sql .= "AND position >= $position ";
		$sql .= "AND id != $id";
		$this->db->query($sql);
		$this->db->query("UPDATE `".$this->table."` SET parent_id = $ref_id, position = $position WHERE id = $id");
		$this->db->query("SET @a=-1");
		$this->db->query("UPDATE `".$this->table."` SET position = @a:=@a+1 WHERE `parent_id` = $ref_id ORDER BY position");
		return true;
	}

}

class json_tree extends _tree_struct { 
	function __construct($table = "tree", $fields = array(), $add_fields = array("title" => "title", "type" => "type", "datas" => "datas")) {
		parent::__construct($table, $fields);
		$this->fields = array_merge($this->fields, $add_fields);
		$this->add_fields = $add_fields;
	}

	function create_node($data) {
		$id = parent::_create((int)$data[$this->fields["id"]], (int)$data[$this->fields["position"]]);
		if($id) {
			$data["id"] = $id;
			$this->set_data($data);
			return  "{ \"status\" : 1, \"id\" : ".(int)$id." }";
		}
		return "{ \"status\" : 0 }";
	}

	function add_node($parent_id, $title) {
		$id = parent::_create((int)$parent_id, (int) $this->max_pos($parent_id));
		if($id) {
            $data = array('id' => $id, 'title' => $title, 'type' => 'default');
			$this->set_data($data);
			return  true;
		}
		return false;
	}

	function add_folder($parent_id, $title) {
		$id = parent::_create((int)$parent_id, (int) $this->max_pos($parent_id));
		if($id) {
            $data = array('id' => $id, 'title' => $title, 'type' => 'folder');
			$this->set_data($data);
			return  true;
		}
		return false;
	}

    function max_pos($parent_id) {
        $this->db->query("SELECT IFNULL(MAX(position+1),0) AS position FROM `tree` WHERE parent_id = $parent_id");
        $this->db->nextr();
        $res =  $this->db->get_row("assoc");
        return $res['position'];
    }

	function set_data($data) {
		if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
		$s = "UPDATE `".$this->table."` SET `".$this->fields["id"]."` = `".$this->fields["id"]."` "; 
		foreach($this->add_fields as $k => $v) {
			if(isset($data[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($data[$k])."\" ";
			else					$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
		}
		$s .= "WHERE `".$this->fields["id"]."` = ".(int)$data["id"];
		$this->db->query($s);
		return "{ \"status\" : 1 }";
	}
	function rename_node($data) { return $this->set_data($data); }

	function move_node($data) { 
		$id = parent::_move((int)$data["id"], (int)$data["ref"], (int)$data["position"], (int)$data["copy"]);
		if(!$id) return "{ \"status\" : 0 }";
		if((int)$data["copy"] && count($this->add_fields)) {
			$ids	= array_keys($this->_get_children($id, true));
			$data	= $this->_get_children((int)$data["id"], true);

			$i = 0;
			foreach($data as $dk => $dv) {
				$s = "UPDATE `".$this->table."` SET `".$this->fields["id"]."` = `".$this->fields["id"]."` "; 
				foreach($this->add_fields as $k => $v) {
					if(isset($dv[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($dv[$k])."\" ";
					else				$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
				}
				$s .= "WHERE `".$this->fields["id"]."` = ".$ids[$i];
				$this->db->query($s);
				$i++;
			}
		}
		return "{ \"status\" : 1, \"id\" : ".$id." }";
	}
	function remove_node($data) {
		$id = parent::_remove((int)$data["id"]);
		return "{ \"status\" : 1 }";
	}
    
    function get_name_from_node_id($arrayid) {
        
		$this->db->query("SELECT title, id FROM `".$this->table."` WHERE id IN (".implode(",", $arrayid).")");
		while($this->db->nextr()) {
            $results[] =  $this->db->get_row("assoc");
        }
        return $results;
    }

	function get_children($data) {
		$tmp = $this->_get_children((int)$data["id"]);
		if((int)$data["id"] === 1 && count($tmp) === 0) {
			$this->_create_default();
			$tmp = $this->_get_children((int)$data["id"]);
		}
		$result = array();
		if((int)$data["id"] === 0) return json_encode($result);
		foreach($tmp as $k => $v) {
			$result[] = array(
				"attr" => array("id" => "node_".$v['id'], "rel" => $v[$this->fields["type"]]),
				"data" => $v[$this->fields["title"]],
				"state" => ($v[$this->fields["type"]] == "default" ? "leaf" : "closed")
			);
		}
		return json_encode($result);
	}

	function searchfield($data) {
		$result = array();
		$this->db->query("SELECT DISTINCT(`".$this->fields["title"]."`) FROM `".$this->table."` WHERE `".$this->fields["title"]."` LIKE '%".$this->db->escape($data)."%' LIMIT 30");
		if($this->db->nf() === 0) return "[]";
		while($this->db->nextr()) {
			$result[] = array('id' => $this->db->f(0), 'label' => $this->db->f(0), 'value' => $this->db->f(0));
		}
		return json_encode($result);
	}
	
	function search($data) {
		$parents = array();
		$this->db->query("SELECT `".$this->fields["id"]."` FROM `".$this->table."` WHERE `".$this->fields["title"]."` LIKE '%".$this->db->escape($data["search_str"])."%'");
		if($this->db->nf() === 0) return "[]";
		while($this->db->nextr()) {
			$parents = array_merge($parents, $this->get_parents($this->db->f(0)));
		}
		$result = array();
		foreach( $parents as $id) { $result[] = "#node_".$id; }
		return json_encode($result);
	}
	
	function get_parents($parent_id) {
		$ids = array();
		while ($parent_id != 0) {
			$this->db->query("SELECT parent_id FROM `".$this->table."` WHERE id = $parent_id ");
        	$this->db->nextr();
			$parent_id = $this->db->f(0);
			$ids[] = $parent_id;
		}
		return $ids;
	}

	function _create_default() {
		$this->_drop();
		$this->create_node(array(
			"id" => 1,
			"position" => 0,
			"title" => "BNP Arbitrage",
			"type" => "drive"
		));
	}

	function _drop() {
		$this->db->query("TRUNCATE `".$this->table."`");
	}
}

?>
