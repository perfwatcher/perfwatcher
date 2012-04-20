<?php
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
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */

require "etc/config.default.php";
require "lib/class._database.php";
require "lib/class.tree.php";
require_once("MDB2.php");

function connect_dbcollectd() {
    global $dbcollectd, $collectd_db_config;
    $options = array('debug' => 2, 'result_buffering' => false);
    if (!isset($dbcollectd)) {
        $dbcollectd = MDB2::factory($collectd_db_config, $options);
        if (PEAR::isError($dbcollectd)) {
            die("[DB Collectd connexion] " . $dbcollectd->getMessage()."\n");
        }
		/*
		$options2 = array('wait' => 'NO WAIT', 'rw' => 'READ ONLY');
		$options2 = array('wait' => 'WAIT', 'rw' => 'READ WRITE');
		$isolation_level = 'READ COMMITTED'; // # (prevents dirty reads)
		$isolation_level = 'REPEATABLE READ'; // # (prevents nonrepeatable reads)
		$isolation_level = 'SERIALIZABLE'; // # (prevents phantom reads)
		$isolation_level = 'READ UNCOMMITTED'; // # (allows dirty reads)
		$dbcollectd->setTransactionIsolation($isolation_level, $options2);
		*/
    }
}

function load_datas($host) {
    global $rrds_path, $grouped_type;
    $ret = array();
    $dh = scandir($rrds_path.'/'.$host, 1);
    foreach ($dh as $plugindir) {
        if (!is_dir($rrds_path.'/'.$host.'/'.$plugindir) || $plugindir == '.' || $plugindir == '..') { continue; }
        $plugin = $plugin_instance = '';
        @list($plugin, $plugin_instance ) = split('-', $plugindir, 2);
        if ($plugin_instance == '') { $plugin_instance = '_'; }
        $ret[$plugin][$plugin_instance] = array();
        $dh2 = scandir($rrds_path.'/'.$host.'/'.$plugindir);
        foreach ($dh2 as $rrd) {
                if ($rrd == '.' || $rrd == '..' || substr($rrd, -4) != '.rrd') { continue; }
                $type = $type_instance = '';
                @list($type, $type_instance) = split('-', substr($rrd,0, -4), 2);
                //echo "$type, $type_instance<br/>\n";
                if ($type_instance == '') { $type_instance = '_'; }
                //if ($type == $plugin) { $type_instance = '_'; }
                if (in_array($type,$grouped_type)) {
                    $ret[$plugin][$plugin_instance][$type]['_'] = true;
                } else {
                    $ret[$plugin][$plugin_instance][$type][$type_instance] = true;
                }
                ksort($ret[$plugin]);
        }
    }
    ksort($ret);
    return $ret;
}

function purge_data() {
    
}

function get_types() {
	global $dbcollectd;
	$types = array();
	connect_dbcollectd();
    $res = $dbcollectd->query("SELECT * FROM types_db");
    if (PEAR::isError($res)) {
        die("[DB Collectd query] " . $res->getMessage()."\n");
    }
	while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		if(!isset($types[$row['type']])) { $types[$row['type']] = array(); }
		$types[$row['type']][] = $row['ds'];
	}
	return $types;
}

function get_grouped_type_instances($type) {
	global $dbcollectd;
	$type_instances = array();
	connect_dbcollectd();
    $res = $dbcollectd->query("SELECT DISTINCT(type_instance) AS type_instance  FROM `plugin_view` WHERE  `type` = '$type'");
    if (PEAR::isError($res)) {
        die("[DB Collectd query] " . $res->getMessage()."\n");
    }
	while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$type_instances[] = $row['type_instance'];
	}
	return $type_instances;
}

function split_pluginstr($pluginstr) {
	if (strpos($pluginstr, '/') === false) {
		return array('', '', '', '');
	}
	list($g, $d) = split('/', $pluginstr, 2);
	if (strpos($g, '-') !== false) {
		list($plugin, $plugin_instance) = split('-', $g, 2);
	} else { $plugin = $g; $plugin_instance = ''; }
	if (strpos($d, '-') !== false) {
		list($type, $type_instance) = split('-', $d, 2);
	} else { $type = $d; $type_instance = ''; }
	return array($plugin, $plugin_instance, $type, $type_instance);
}

function get_childrens_plugins($jstree, $parentid, $grouped = true) {
    global $dbcollectd, $grouped_type, $childrens_cache;
    connect_dbcollectd();
    $query = "SELECT DISTINCT(CONCAT(plugin,'-',plugin_instance,'/',type,'-',type_instance)) AS plugin
                FROM snap_data_view
                WHERE host
                IN('";
    if (!is_array($parentid) && substr($parentid, 0, 11) == 'aggregator_') {
        $childrens = $jstree->_get_children(substr($parentid, 11), true);
        $hosts = array();
        foreach($childrens as $children) {
            if ($children['type'] == 'default') {
                $hosts[] = $children['title'];
            }
        }
    } elseif(!is_array($parentid)) {
        $hosts[] = $parentid; 
    } else {
        $hosts = $parentid;
    }
    $query .= implode("','", $hosts)."') ORDER BY plugin";
    //echo "$query\n";
    $res = $dbcollectd->query($query);
    if (PEAR::isError($res)) {
        die("[DB Collectd query] " . $res->getMessage()."\n");
    }
    $plugins = array();
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        list($g, $d) = split('/', $row['plugin'], 2);
        list($plugin, $plugin_instance) = split('-', $g, 2);
        list($type, $type_instance) = split('-', $d, 2);
        $tplugin = $plugin.($plugin_instance != '' ? '-'.$plugin_instance : '').'/'.$type.(in_array($type,$grouped_type) || $type_instance == '' ? '' : '-'.$type_instance);
        if (!isset($plugins[$tplugin])) {
            $plugins[$tplugin] = array($plugin, $plugin_instance, $type, (in_array($type,$grouped_type) ? '' : $type_instance), $tplugin);
        }
    }
    return $plugins;
}

function get_load($host) {
    global $dbcollectd;
    connect_dbcollectd();
    $load = 0;
    $query = "SELECT value FROM  snap_data_view WHERE  host LIKE  '$host' 
    AND  plugin LIKE  'load' AND  type LIKE  'load' AND  dataset_name LIKE  'shortterm'";
    $res = $dbcollectd->query($query);
    if (PEAR::isError($res)) {
        return $load;
    }
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        return $row['value'];
    }
    return $load;
}

function get_status($arrayid, $return_title = false) {
    global $jstree, $rrds_path, $dbcollectd;
    connect_dbcollectd();
    $ret = array('up' => array(), 'down' => array(), 'unknown' => array());
    foreach ($arrayid as $key => $val) {
        if ($val == '') { unset($arrayid[$key]); }
    }
    if(count($arrayid) == 0) { return $ret; }
    //if(!isset($_GET['debug'])) { return $ret; }
    $time = time();
    $hosts = array();
    $arrayid = $jstree->get_name_from_node_id($arrayid);
    foreach ($arrayid as $node) { $hosts[] = $node['title']; }
    $query = "SELECT DISTINCT(host), UNIX_TIMESTAMP(MAX(date)) AS date FROM data_view WHERE host IN ('".implode("','", $hosts)."') GROUP BY host";
    $res = $dbcollectd->query($query);
    $dates = array();
    while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $dates[$row['host']] = $row['date'];
    }
    foreach ($arrayid as $node) {
        if (!isset($dates[$node['title']])) {
            $ret['unknown'][] = $return_title ? $node['title'] : $node['id'];
        } else {
            if ($dates[$node['title']] < $time - 300) {
                $ret['down'][] = $return_title ? $node['title'] : $node['id'];
            } else {
                $ret['up'][] = $return_title ? $node['title'] : $node['id'];
            }
        }
    }
    return $ret;
}

function get_cpu_count($host_id)
{
    global $jstree, $dbcollectd, $childrens_cache;
    connect_dbcollectd();
	$childrens = $jstree->_get_children($host_id, true);
    if (count($childrens) != 0) {
        $hosts = array();
        foreach($childrens as $children) {
            if ($children['type'] == 'default') {
                $hosts[] = $children['title'];
            }
        }
        $hostsstr = implode("','", $hosts);
    } else { $hostsstr = get_node_name_by_id($host_id); }
	$res = $dbcollectd->query("SELECT SUM(value) as nbcpu  FROM snap_data_view WHERE type = 'nbcpu' AND plugin = 'cpu' AND host IN ('$hostsstr')");
	if (PEAR::isError($res)) {
	    print("[DB Collectd query] " . $res->getMessage()."\n");
		return(0);
	}
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        return is_numeric($row['nbcpu']) ? $row['nbcpu'] : 0;
    }
    return 0;
}

function get_nodes_count($host_id)
{
    global $jstree, $childrens_cache;
    $nodes = 0;
    $childrens = $jstree->_get_children($host_id, true);
    foreach($childrens as $children) {
        if ($children['type'] != 'default') {
            continue;
        }
        $nodes++;
    }
    return $nodes;
}

function get_process_count($host_id)
{
    global $jstree, $dbcollectd, $childrens_cache;
    connect_dbcollectd();
	$childrens = $jstree->_get_children($host_id, true);
	$hosts = array();
    foreach($childrens as $children) {
        if ($children['type'] == 'default') {
            $hosts[] = $children['title'];
        }
    }
	$hostsstr = implode("','", $hosts);
	$query = "SELECT SUM(value) AS process FROM snap_data_view WHERE plugin = 'processes' AND type = 'ps_state' AND type_instance = 'running' AND  host IN ('$hostsstr')";
	//echo "$query\n";
	$res = $dbcollectd->query($query);
	if (PEAR::isError($res)) {
	    print("[DB Collectd query] " . $res->getMessage()."\n");
		return(0);
	}
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        return isset($row['process']) && is_numeric($row['process']) ? $row['process'] : 0;
    }
    return 0;
}

function get_node_name($id) {

    if (substr($id, 0, 11) == 'aggregator_') {
        $id = substr($id, 11);
    } else { return $id; }
    $jstree = new json_tree();
    $node = $jstree->_get_node($id);
    return $node['title'];
}

function get_node_name_by_id($id) {
    $jstree = new json_tree();
    $node = $jstree->_get_node($id);
    return $node['title'];
}

?>
