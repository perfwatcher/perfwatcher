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

require "etc/config.default.php";
require "lib/compat._database.php";
require "lib/class._database.php";
require "lib/class.tree.php";
require "lib/class.selections.php";
require "lib/class.json_item_datas.php";
require_once("MDB2.php");

function pw_error_log($msg, $file="unset", $line="unset", $fct="unset") {
    error_log(date("Y/m/d H:i:s")." ($file:$line:$fct) $msg\n", 3, "logs/perfwatcher.log");
}

function get_arg($key, $default_value, $check_if_is_numeric, $die_error_message, $file="unset", $line="unset") {
    if(isset($_GET[$key])) {
        if($check_if_is_numeric && (! is_numeric($_GET[$key]))) {
            if($die_error_message) { pw_error_log("$die_error_message", $file,$line); exit(1); }
            return($default_value); # return default if no die message.
        }
        return($_GET[$key]);
    } elseif(isset($_POST[$key])) {
        if($check_if_is_numeric && (! is_numeric($_POST[$key]))) {
            if($die_error_message) { pw_error_log("$die_error_message", $file,$line); exit(1); }
            return($default_value); # return default if no die message.
        }
        return($_POST[$key]);
    }
    if($die_error_message) { pw_error_log("$die_error_message", $file,$line); exit(1); }
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
    $ra = array(null, null, null);
    foreach ($sources as $collectd_source_alias => $collectd_source_data) {
        if(! isset($collectd_source_data["jsonrpc"])) {
            continue;
        }
        $jsonrpc_url = $collectd_source_data{"jsonrpc"};
        $jsonrpc_httpproxy = isset($collectd_source_data{"proxy"})?$collectd_source_data{"proxy"}:null;

        $ch = curl_init($jsonrpc_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $jsonrpc_httpproxy == null ? FALSE : TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_encoded_request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json_encoded_request))
                );

        /* Send the request */
        if($result = curl_exec($ch)) {
            if ($result  != '' && $result = json_decode($result, true)) {
                if(isset($result['result'])) {
                    $ra = array($result['result'], $collectd_source_alias, null);
                    curl_close($ch);
                    break;
                } else {
                    pw_error_log("Failed to query the following request to ".$collectd_source_data{"jsonrpc"}." (source $collectd_source_alias)", __FILE__, __LINE__, __FUNCTION__);
                    pw_error_log("\$json_encoded_request='$json_encoded_request'", __FILE__, __LINE__, __FUNCTION__);
                    pw_error_log("\$result=".json_encode($result), __FILE__, __LINE__, __FUNCTION__);
                }
            }
        } else {
            pw_error_log("Failed to query the following request to ".$collectd_source_data{"jsonrpc"}." (source $collectd_source_alias). Error was '".curl_error($ch)."'", __FILE__, __LINE__, __FUNCTION__);
            pw_error_log("$json_encoded_request='$json_encoded_request'",__FILE__, __LINE__, __FUNCTION__);
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
        $local_collectd_sources = array_keys($collectd_sources);
    }

    foreach ($local_collectd_sources as $collectd_source) {

        $json = json_encode(array(
                    "jsonrpc" => "2.0",
                    "method" => "pw_get_dir_hosts",
                    "params" => "",
                    "id" => 0)
                );

        $ra = jsonrpc_query($collectd_source, $json);

        if(!(isset($ra[0]) && isset($ra[1]))) { continue; }
        $r = $ra[0];
        if (! isset($r['nb'])) { continue; }

        $data = $r['values'];
        if($data) {
            if($include_aggregators) {
                foreach ($data as $h) {
                    $ret[$h][] = $collectd_source;
                }
            } else {
                foreach ($data as $h) {
                    if(substr($h, 0, 11) != "aggregator_") $ret[$h][] = $collectd_source;
                }
            }
        }
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

function get_widget($res) {
    global $widgets;
    $json = array();
    foreach($widgets as $widget) {
        if (!file_exists("lib/class.$widget.php")) { continue; }
        if (class_exists ($widget)) { continue; }
        if (!include ("lib/class.$widget.php")) { continue; }
        if (!class_exists ($widget)) { continue; }
        $owidget = new $widget($res);
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

function create_new_view($title) {
    global $db_config;
    $view_id = -1;
    $id = -1;
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $id = $db->insert_id_before('tree', 'id', "in create_new_view()");
        $db->prepare("INSERT INTO tree (id, view_id, parent_id, position, pwtype, title) VALUES (?, (SELECT MAX(view_id)+1 FROM tree), 1, 0, 'container', ?)", array('integer', 'text'));
        $db->execute(array((int)$id, $title));
        $id = $db->insert_id_after($id, 'tree', 'id', "in create_new_view()");
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

function list_view_roots() {
    global $db_config;
    $r = array();
    $db = new _database($db_config);
    if ($db->connect()) {
        $result_connect = 1;
        $db->query("SELECT id,view_id,title FROM tree WHERE parent_id = 1");
        while($db->nextr()) {
            $v = $db->get_row('assoc');
            $r[] = $v;
        }
        $db->destroy();
    }
    return($r);
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

/*
 * Get all rrd files on Collectd servers.
 * Then ask Collectd servers for a status.
 * This is useful to get the list of dead servers.
 */
function get_dead_servers_list($collectd_source_forced=null, $include_aggregators=0, $return_unknown_only=1) {
    global $collectd_sources;

    $ret = array();
    if($collectd_source_forced) {
        $local_collectd_sources[] = $collectd_source_forced;
    } else {
        $local_collectd_sources = array_keys($collectd_sources);
    }

    foreach ($local_collectd_sources as $collectd_source) {
        $rrd_list = array();

        // Get list of rrd hosts
        $json = json_encode(array(
                    "jsonrpc" => "2.0",
                    "method" => "pw_get_dir_hosts",
                    "params" => "",
                    "id" => 0)
                );

        $ra = jsonrpc_query($collectd_source, $json);

        if(!(isset($ra[0]) && isset($ra[1]))) { continue; }
        $r = $ra[0];
        if (! isset($r['nb'])) { continue; }

        $data = $r['values'];
        if($data) {
            if($include_aggregators) {
                foreach ($data as $h) {
                    $rrd_list[] = $h;
                }
            } else {
                foreach ($data as $h) {
                    if(substr($h, 0, 11) != "aggregator_") $rrd_list[] = $h;
                }
            }
        }

        // Ask status for all rrd hosts
        $json = json_encode(array(
                    "jsonrpc" => "2.0",
                    "method" => "pw_get_status",
                    "params" => array(
                        "timeout" => 240,
                        "server" => $rrd_list,
                        ),
                    "id" => 0)
                );

        $ra = jsonrpc_query($collectd_source, $json);

        if(!(isset($ra[0]) && isset($ra[1]))) { continue; }
        $data = $ra[0];

        if($data) {
            if($return_unknown_only) {
                foreach ($data as $h => $r) {
                    if($r == "unknown") {
                        $ret[$collectd_source][$h] = $r;
                    }
                }
            } else {
                foreach ($data as $h => $r) {
                    $ret[$collectd_source][$h] = $r;
                }
            }
        }

        ksort($ret[$collectd_source]);
    }
    ksort($ret);

    return $ret;
}

/*
 * Check if aggregators are allowed for this Collectd source
 */
function is_aggregator_allowed($cdsrc) {
    global $collectd_sources;
# unknown source
    if(! isset($collectd_sources[$cdsrc])) return(0); 

# known source; enabled by default
    if(! isset($collectd_sources[$cdsrc]['no_aggregator'])) return(1);

# First letter is 'y' so we disable it.
    if(strtolower(substr($collectd_sources[$cdsrc]['no_aggregator'],0,1)) == 'y') return(0);

# option is enabled, so we disable the aggregator
    if($collectd_sources[$cdsrc]['no_aggregator'] == 1) return(0);

# All other values means that we enable the aggregator
    return(1);
}
?>
