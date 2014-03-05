#! /usr/bin/php
<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et :

require "lib/common.php";
global $collectd_source_default;

function show_config_error_msg() {
    echo "Please update your etc/config.php first.\n";
    echo "You need to set \$collectd_source_default and \$collectd_sources\n";
    echo "This script will set all aggregators to \$collectd_source_default\n";
    exit;
}

function read_value_from_stdin($prompt, $valid, $default) {
    print "$prompt ";
    while(!isset($rv) || (is_array($valid) && !in_array($rv, $valid))) {
        if(isset($rv)) {
            print "'$rv' is not a valid value\n";
            print "$prompt ";
        }
        $rv = trim(fgets(STDIN));
        if(empty($rv) && !empty($default)) {
            $rv = $default;
        }
    }
    return($rv);
}

if(!isset($collectd_source_default)) {
    show_config_error_msg();
}
if(!isset($collectd_sources)) {
    show_config_error_msg();
}

$defaultcdsrc = $collectd_source_default;
$defaultcdsrc = read_value_from_stdin(
        "What Collectd source do you want to use for aggregators ? (default='$collectd_source_default')",
        array_keys($collectd_sources),
        $collectd_source_default
        );
$dropemptytabs = 'n';
$dropemptytabs = read_value_from_stdin(
        "Do you want to drop empty tabs ? (default='yes')",
        array('yes', 'no', 'y', 'n'),
        'y'
        );
if(substr($dropemptytabs,0,1) == 'y') {
    $dropemptytabs = 1;
} else {
    $dropemptytabs = 0;
}

echo "You will use the following parameters :\n";
echo "  default Collectd source : '$defaultcdsrc'\n";
echo "  drop empty tabs         : ".($dropemptytabs?'yes':'no')."\n";

$confirm = read_value_from_stdin("Confirm ? (y/N)", array('y', 'n', 'N'), 'n');
if($confirm != 'y') { 
    echo "Abandon...\n";
    exit;
}



$db = new _database($db_config);
$aggregator = array();
$tabs = array();

if ($db->connect()) {

        echo "Working on aggregators\n";

# Update the database schema
    $sql_requests = array(
            "ALTER TABLE tree ADD pwtype varchar(255) DEFAULT NULL AFTER type",
            "UPDATE tree set pwtype = 'server' where type = 'default'",
            "UPDATE tree set pwtype = 'container' where type <> 'default'",
            "UPDATE tree set type = 'folder' where type = 'drive'",
            "ALTER TABLE tree DROP type",
            "ALTER TABLE tree ADD INDEX pwtype (pwtype)",
            "ALTER TABLE tree ADD agg_id bigint(20) unsigned DEFAULT NULL AFTER pwtype",
            "ALTER TABLE tree ADD INDEX agg_id (agg_id)",
            "CREATE TABLE selections ("
                ."id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,"
                ."tree_id      bigint(20) unsigned NOT NULL,"
                ."title        varchar(255) DEFAULT NULL,"
                ."sortorder    bigint(20) unsigned NOT NULL,"
                ."deleteafter  bigint(20) unsigned NOT NULL,"
                ."data         text                NOT NULL,"
                ."PRIMARY KEY (id)"
                .");",
            "CREATE TABLE config ("
                ."confkey      varchar(255) NOT NULL,"
                ."value        text         NOT NULL,"
                ."PRIMARY KEY  (confkey)"
                .");",
            "INSERT INTO config (confkey, value) VALUES ('schema_version', '1.0')",
            );


    foreach($sql_requests as $sql) {
        if ( ! $db->query($sql) ) { echo "Failed with this request :\n$sql\n"; exit; }
    }

# Aggregators
    $db->prepare("SELECT * from tree where pwtype = 'container' AND datas <> 'a:0:{}' AND datas like '%plugin%'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $datas = unserialize($r['datas']);
        if(isset($datas['plugins'])) {
            $aggregator[] = $r;
        }
    }
    $db->free();

# Update aggregators
    foreach ($aggregator as $a) {
        echo "  Updating item ".$a['id']." '".$a['title']."'\n";
        $a_datas = unserialize($a['datas']);

        foreach ($a_datas as $k => $v) {
            if($k == 'plugins') {
                foreach ($v as $agg => $bool) {
                    $a_datas['aggregators'][$defaultcdsrc."/".$agg] = array('CdSrc' => $defaultcdsrc, 'plugin' => $agg);
                }
            }
        }
        if(isset($a_datas['plugins'])) {
            unset($a_datas['plugins']);
        }
# Update the aggregator datas
        $db->prepare(
                "UPDATE tree SET datas = ?, agg_id = ? WHERE id = ?",
                array('text', 'integer' , 'integer')
                );

        $db->execute(array(
                    serialize($a_datas),
                    $a['id'],
                    $a['id']
                    ));
        $db->free();
    }

        echo "Working on tabs\n";
# Tabs
    $db->prepare("SELECT * from tree where datas <> 'a:0:{}' AND datas like '%tab_title%'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $datas = unserialize($r['datas']);
        if(isset($datas['tabs'])) {
            $tabs[] = $r;
        }
    }
    $db->free();

    foreach ($tabs as $t) {
        echo "  Updating item ".$t['id']." '".$t['title']."'\n";
        $t_datas = unserialize($t['datas']);

        foreach ($t_datas as $k => $v) {
            if($k == 'tabs') {
                $pwtype = "";

                # Check for non empty tabs
                $sometabsnotempty=0;
                foreach ($v as $tab_title => $tab_description) {
                    if(isset($tab_description['selected_graph']) && $tab_description['selected_graph']) {
                        if($t['pwtype'] != "container") { $sometabsnotempty = 1; break; }
                        if(isset($tab_description['selected_hosts']) && (count($tab_description['selected_hosts']) >= 1)) { $sometabsnotempty = 1; break; }
                        if(isset($tab_description['selected_aggregators']) && (count($tab_description['selected_aggregators']) >= 1)) { $sometabsnotempty = 1; break; }
                    }
                }
                if($t['pwtype'] == "container") {
                    $db->prepare("SELECT COALESCE(MAX(position+1),0) AS position FROM tree WHERE view_id = ? AND parent_id = ?", array('integer', 'integer'));
                    $db->execute(array((int)$t['view_id'], $t['parent_id']));
                    $db->nextr();
                    $res =  $db->get_row("assoc");
                    $pos =  $res['position'];

                    if($sometabsnotempty || ($dropemptytabs == 0)) {
                        $db->prepare("INSERT into tree (view_id, parent_id, position, title, pwtype) VALUES (?, ?, ?, ?, ?)", array('integer', 'integer', 'integer', 'text', 'text'));
                        $db->execute(array((int)$t['view_id'], (int)$t['id'], (int)$pos, 'tabs_from_'.$t['title'], 'selection'));
                        $tree_id = $db->insert_id('tree', 'id');
                        $pwtype = "container";
                    }
                } else {
                    $tree_id = $t['id'];
                    $pwtype = "server";
                }

                if($sometabsnotempty || ($dropemptytabs == 0)) {
                    foreach ($v as $tab_title => $tab_description) {
                        $deleteafter = isset($tab_description['deleteafter'])?$tab_description['deleteafter']:0;
                        $title = isset($tab_description['tab_title'])?$tab_description['tab_title']:"no_title";
                        $markup = "";
                        if(isset($tab_description['selected_graph']) && $tab_description['selected_graph'] && ($pwtype == "server")) {
                            $selected_graphs = $tab_description['selected_graph'];
                            if(!is_array($selected_graphs)) {
                                $selected_graphs = explode(',', $tab_description['selected_graph']);
                            }
                            foreach ($selected_graphs as $tab_graph) {
                                if(is_array($tab_graph)) {
                                    $gd = $tab_graph;
                                } else {
                                    $gd = explode("|", $tab_graph);
                                }
                                if(count($gd) >= 3) {
                                    $markup .= "rrdgraph('$defaultcdsrc', '$tab_host', '$gd[0]', '$gd[1]', '$gd[2]', '$gd[3]')\n";
                                }
                            }
                        }
                        if(isset($tab_description['selected_graph']) && $tab_description['selected_graph'] && isset($tab_description['selected_hosts'])) {
                            $markup .= "<table>\n";
                            foreach ($tab_description['selected_hosts'] as $tab_host) {
                                $markup .= "  <tr>\n";
                                $selected_graphs = $tab_description['selected_graph'];
                                if(!is_array($selected_graphs)) {
                                    $selected_graphs = explode(',', $tab_description['selected_graph']);
                                }
                                foreach ($selected_graphs as $tab_graph) {
                                    if(is_array($tab_graph)) {
                                        $gd = $tab_graph;
                                    } else {
                                        $gd = explode("|", $tab_graph);
                                    }
                                    if(count($gd) >= 3) {
                                        $markup .= "    <td>rrdgraph('$defaultcdsrc', '$tab_host', '$gd[0]', '$gd[1]', '$gd[2]', '$gd[3]')</td>\n";
                                    }
                                }
                                $markup .= "  </tr>\n";
                            }
                            $markup .= "</table>\n";
                        }
                        if(isset($tab_description['selected_graph']) && $tab_description['selected_graph'] && isset($tab_description['selected_aggregators'])) {
                            $markup .= "<table>\n";
                            foreach ($tab_description['selected_aggregators'] as $tab_host) {
                                $markup .= "  <tr>\n";
                                $selected_graphs = $tab_description['selected_graph'];
                                if(!is_array($selected_graphs)) {
                                    $selected_graphs = explode(',', $tab_description['selected_graph']);
                                }
                                foreach ($selected_graphs as $tab_graph) {
                                    if(is_array($tab_graph)) {
                                        $gd = $tab_graph;
                                    } else {
                                        $gd = explode("|", $tab_graph);
                                    }
                                    if(count($gd) >= 3) {
                                        $markup .= "    <td>rrdgraph('$defaultcdsrc', '$tab_host', '$gd[0]', '$gd[1]', '$gd[2]', '$gd[3]')</td>\n";
                                    }
                                }
                                $markup .= "  </tr>\n";
                            }
                            $markup .= "</table>\n";
                        }
                        if(($dropemptytabs == 0) || $markup) {
                            if($markup) {
                                if($t['pwtype'] == "container") $markup = "WARNING : this tab was converted from a container. You may edit it and use regex for hosts definition.\n\n".$markup;
                                $markup = "# Converted from older Perfwatcher tab #\n".$markup;
                            }
                            $data = array('markup' => $markup);
# Insert a new selection
                            $db->prepare("INSERT INTO selections (title, tree_id, deleteafter, data) VALUES (?, ?, ?, ?)", array('text', 'integer', 'integer', 'text'));
                            $db->execute(array($title, (int)$tree_id, (int)$deleteafter, serialize($data)));
                            $db->free();
                        }
                    }
                }
            }
        }
        if(isset($t_datas['tabs'])) {
            unset($t_datas['tabs']);
        }
# Update the item datas
        $db->prepare(
                "UPDATE tree SET datas = ? WHERE id = ?",
                array('text', 'integer')
                );

        $db->execute(array(
                    serialize($t_datas),
                    $t['id']
                    ));
    }
    $db->destroy();
}



?>
