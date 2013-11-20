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
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */

require "etc/config.default.php";
require "lib/class._database.php";
require "lib/class.tree.php";
require_once("MDB2.php");

function get_arg($key, $default_value, $check_if_is_numeric, $die_error_message, $file="unset", $line="unset") {
    if(isset($_GET[$key])) {
        if($check_if_is_numeric && (! is_numeric($_GET[$key]))) {
            if($die_error_message) { error_log("$die_error_message ($file,$line)\n"); exit(1); }
            return($default_value); # return default if no die message.
        }
        return($_GET[$key]);
    } elseif(isset($_POST[$key])) {
        if($check_if_is_numeric && (! is_numeric($_POST[$key]))) {
            if($die_error_message) { error_log("$die_error_message ($file,$line)\n"); exit(1); }
            return($default_value); # return default if no die message.
        }
        return($_POST[$key]);
    }
    if($die_error_message) { error_log("$die_error_message ($file,$line)\n"); exit(1); }
    return($default_value); # return default if no die message.

}

function exit_jsonrpc_error($error) {
#    file_put_contents('php://stderr', "exit_jsonrpc_error : $error\n");
    echo json_encode(array("error" => $error, "data" => array())); exit;
}

function jsonrpc_query($source = null, $json_encoded_request) {
    /* Returns an array $ret[$plugin][$plugin_instance][$type][$type_instance].
     * If $type is in the array $blacklisted_type, the item is not inserted.
     * If $type is in the array $grouped_type, the $type_instance is set to "_".
     */
    global $collectd_sources;

    putenv('http_proxy');
    putenv('https_proxy');
    if($source) {
        $sources = array("$source" => $collectd_sources[$source]);
    } else {
        $sources = $collectd_sources;
    }
    foreach ($sources as $collectd_source_alias => $collectd_source_data) {
        if(! isset($collectd_source_data["jsonrpc"])) {
            next;
        }
        $jsonrpc_url = $collectd_source_data{"jsonrpc"};
        $ch = curl_init($jsonrpc_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $jsonrpc_httpproxy == null ? FALSE : TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_encoded_request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json_encoded_request))
                );

        /* Send the request */
        $ra = array(null, null, null);
        if($result = curl_exec($ch)) {
            if ($result  != '' && $result = json_decode($result, true)) {
                if(!isset($result['result'])) file_put_contents("php://stderr", "Bug !!!\n\$json_encoded_request='$json_encoded_request'\n\$result=".print_r($result,1)."\nToo bad !\n");
                $ra = array($result['result'], $collectd_source_alias, null);
                break;
            }
        } else {
            exit_jsonrpc_error(curl_error($ch));
        }
        curl_close($ch);
    }
    return($ra);
}


function get_list_of_types_instances($collectd_source, $host, $plugin, $plugin_instance, $type = null) {
    /* Returns an array with/without aggregators as $ret[host] = array(source1, source2...)
     */
    global $collectd_sources;

    $plugin_and_instance = $plugin.((isset($plugin_instance) && ($plugin_instance != ""))?"-$plugin_instance":"");

    $ret = array();

    $json = json_encode(array(
                "jsonrpc" => "2.0",
                "method" => "pw_get_dir_types",
                "params" => array( "hostname" => $host, "plugin" => $plugin_and_instance ),
                "id" => 0)
            );
    $ra = jsonrpc_query($collectd_source, $json);

    if(!(isset($ra[0]) && isset($ra[1]))) { return($ret); }
    $r = $ra[0];
    if (! isset($r['nb'])) { return($ret); }

    $data = $r['values'];
    if($data) {
        if($type) {
            foreach ($data as $t) {
                if (substr($t, strlen($t)-4) != '.rrd') continue;
                $t= substr($t, 0, strlen($t)-4);
                $a = explode("-", $t, 2);
                if($type == $a[0]) {
                    if(isset($a[1])) {
                        $ret[] = $a[1];
                    } else {
                        $ret[] = "";
                    }
                }
            }
        } else {
            foreach ($data as $t) {
                if (substr($t, strlen($t)-4) != '.rrd') continue;
                $ret[] = $t;
            }
        }
    }
    return $ret;
}

function get_list_of_hosts_having_rrds($collectd_source_forced, $include_aggregators) {
    /* Returns an array with/without aggregators as $ret[host] = array(source1, source2...)
     */
    global $collectd_sources;

    $ret = array();
    if($collectd_source_forced) {
        $local_collectd_sources[] = $collectd_source_forced;
    } else {
        $local_collectd_sources = $collectd_sources;
    }

    putenv('http_proxy');
    putenv('https_proxy');
    foreach ($local_collectd_sources as $collectd_source_alias => $collectd_source_data) {
        if(! isset($collectd_source_data["jsonrpc"])) {
            next;
        }
        $jsonrpc_url = $collectd_source_data{"jsonrpc"};
        $ch = curl_init($jsonrpc_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $jsonrpc_httpproxy == null ? FALSE : TRUE);

        /* Create the request */
        $json = json_encode(array(
                    "jsonrpc" => "2.0",
                    "method" => "pw_get_dir_hosts",
                    "params" => "",
                    "id" => 0)
                );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
                );

        /* Send the request */
        if($result = curl_exec($ch)) {
            if ($result  != '' && $result = json_decode($result)) {
                if (isset($result->result->nb)) {
                    $source = $collectd_source_alias;
                    $data = $result->result->values;
                    if($data) {
                        if($include_aggregators) {
                            foreach ($data as $h) {
                                $ret[$h][] = $source;
                            }
                        } else {
                            foreach ($data as $h) {
                                if(substr($h, 0, 11) != "aggregator_") $ret[$h][] = $source;
                            }
                        }
                    }
                }
            }
        } else {
            exit_jsonrpc_error(curl_error($ch));
        }
        curl_close($ch);
    }
    ksort($ret);

    return $ret;
}

function get_list_of_rrds($collectd_source, $host) {
    /* Returns an array $ret[$plugin][$plugin_instance][$type][$type_instance].
     * If $type is in the array $blacklisted_type, the item is not inserted.
     * If $type is in the array $grouped_type, the $type_instance is set to "_".
     */
    global $grouped_type, $blacklisted_type;

    putenv('http_proxy');
    putenv('https_proxy');
    /* Create the request */
    $json = json_encode(array(
                "jsonrpc" => "2.0",
                "method" => "pw_get_dir_all_rrds_for_host",
                "params" => array("hostname" => $host),
                "id" => 0)
            );
    $ra = jsonrpc_query($collectd_source, $json);

    $ret = array();
    if(!(isset($ra[0]) && isset($ra[1]))) { return($ret); }
    $r = $ra[0];
    if (! isset($r['nb'])) { return($ret); }

    $data = $r['values'];
    if(!isset($data)) { return($ret); }
    foreach ($data as $p => $o1) { /* Plugins */
        foreach ($o1 as $pi => $o2) { /* Plugin instances */
            if($pi === "") { $pi = "_"; }
            foreach ($o2 as $t => $o3) { /* Types */
                if (in_array($t, $blacklisted_type)) { 
                    /* blacklisted type : do nothing */
                } else if(in_array($t, $grouped_type)) {
                    /* grouped type : replace the type instances with only "_" */
                    $ret[$p][$pi][$t]["_"] = true;
                } else {
                    /* Any other type : keep type instances and set them as true */
                    foreach ($o3 as $ti => $o4) {
                        if($ti === '')  {
                            $ret[$p][$pi][$t]["_"] = true;
                        } else {
                            $ret[$p][$pi][$t][$ti] = true;
                        }
                    }
                }
                if(isset($ret[$p][$pi][$t])) ksort($ret[$p][$pi][$t]);
            }
            if(isset($ret[$p][$pi])) ksort($ret[$p][$pi]);
        }
        if(isset($ret[$p])) ksort($ret[$p]);
    }
    if(isset($ret)) ksort($ret);

    return $ret;
}

function purge_data() {

}

function get_widget($datas) {
    global $widgets;
    $json = array();
    foreach($widgets as $widget) {
        if (!file_exists("lib/class.$widget.php")) { continue; }
        if (class_exists ($widget)) { continue; }
        if (!include ("lib/class.$widget.php")) { continue; }
        if (!class_exists ($widget)) { continue; }
        $owidget = new $widget($datas);
        if (!$owidget->is_compatible()) { continue; }
        $json[$widget] = $owidget->get_info();
    }
    return $json;
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

function create_new_view($title) {
    global $db_config;
    $view_id = -1;
    $id = -1;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("INSERT INTO tree (view_id, parent_id, position, type, title) SELECT MAX(view_id)+1, 1, 0, 'folder', ? FROM tree", array('text'));
        $db->execute($title);
        $id = $db->insert_id('tree', 'id');
        $db->prepare("SELECT distinct view_id FROM tree WHERE id = ?", array('integer'));
        $db->execute((int)$id);
        if($db->nextr()) {
            $r = $db->get_row('assoc');
            $view_id = $r['view_id'];
        }
        $db->destroy();
    }
    return(array($id,$view_id));
}

function list_views($maxrows, $startswith) {
    global $db_config;
    $r = array();
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $startswith = $startswith."%";
        $db->prepare("SELECT view_id,title FROM tree WHERE parent_id = 1 AND title LIKE ? ORDER BY title LIMIT ?", array('text', 'integer'));
        $db->execute(array($startswith, $maxrows));
        while($db->nextr()) {
            $v = $db->get_row('assoc');
            $r[] = $v;
        }
        $db->destroy();
    }
    return($r);
}

function delete_view($view_id) {
    global $db_config;
    $result_view_id = 0;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("DELETE FROM tree WHERE view_id = ?", array('integer'));
        $db->execute($view_id);
        $db->prepare("SELECT MIN(view_id) AS v FROM tree");
        $db->execute();
        if($db->nextr()) {
            $r = $db->get_row('assoc');
            $result_view_id = $r['v'];
        }
        $db->destroy();
    }
    return($result_view_id);
}

function get_view_id_from_id($id) {
    global $db_config;
    $view_id = -1;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("SELECT distinct view_id FROM tree WHERE id = ?", array('integer'));
        $db->execute((int)$id);
        if($db->nextr()) {
            $r = $db->get_row('assoc');
            $view_id = $r['view_id'];
        }
        $db->destroy();
    }
    return($view_id);
}

function get_node_name_by_id($id) {
    global $db_config;
    $title = "";
    $id = substr($id, 11);
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->prepare("SELECT title FROM tree WHERE id = ?", array('integer'));
        $db->execute((int)$id);
        if($db->nextr()) {
            $r = $db->get_row('assoc');
            $title = $r['title'];
        }
        $db->destroy();
    }
    return $title;
}

function get_node_name($id) {
    if (substr($id, 0, 11) != 'aggregator_') { return($id); }
    return get_node_name_by_id($id);
}

?>
