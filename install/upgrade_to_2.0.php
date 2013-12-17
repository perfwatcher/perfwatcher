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

echo "You will use the following parameters :\n";
echo "  default Collectd source : '$defaultcdsrc'\n";

$confirm = read_value_from_stdin("Confirm ? (y/N)", array('y', 'n', 'N'), 'n');
if($confirm != 'y') { 
    echo "Abandon...\n";
    exit;
}



$db = new _database($db_config);
$aggregator = array();
$tabs = array();

if ($db->connect()) {


# Update the database schema
    $sql_requests = array(
            "ALTER TABLE tree ADD pwtype varchar(255) DEFAULT NULL AFTER type",
            "UPDATE tree set pwtype = 'server' where type = 'default'",
            "UPDATE tree set pwtype = 'container' where type <> 'default'",
            "ALTER TABLE tree ADD INDEX pwtype (pwtype)",
            "ALTER TABLE tree ADD agg_id bigint(20) unsigned DEFAULT NULL AFTER pwtype",
            "ALTER TABLE tree ADD INDEX agg_id (agg_id)",
            );


    foreach($sql_requests as $sql) {
        if ( ! $db->query($sql) ) { echo "Failed with this request :\n$sql\n"; exit; }
    }

    $db->prepare("SELECT * from tree where pwtype = 'container' AND datas <> 'a:0:{}' AND datas like '%plugin%'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $datas = unserialize($r['datas']);
        if(isset($datas['plugins'])) {
            $aggregator[] = $r;
        }
        if(isset($datas['tabs'])) {
            $tabs[] = $r;
        }
    }
    $db->free();

# Update aggregators
    foreach ($aggregator as $a) {
        echo "Updating item ".$a['id']." '".$a['title']."'\n";
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
    $db->destroy();
}



?>
