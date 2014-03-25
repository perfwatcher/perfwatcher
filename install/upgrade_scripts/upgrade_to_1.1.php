#! /usr/bin/php
<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et :

$schema_version_required = '1.0';
$schema_version_new = '1.1';


chdir(dirname(__FILE__)."/../..");
require "lib/common.php";
global $collectd_source_default;

function show_config_error_msg() {
    echo "Please update your etc/config.php first.\n";
    echo "You need to set \$collectd_source_default and \$collectd_sources\n";
    echo "This script will set all aggregators to \$collectd_source_default\n";
    exit;
}

print "Upgrading from $schema_version_required to $schema_version_new : ";

$db = new _database($db_config);

if ($db->connect()) {
    $db->prepare("SELECT value FROM config WHERE confkey = 'schema_version'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $schema_version = $r['value'];
    }
    $db->free();
    if($schema_version != $schema_version_required) {
        print "Not upgrading to $schema_version_new : found $schema_version\n";
        $db->destroy();
        exit;
    }


# Update the database schema
    $sql_requests = array(
            "ALTER TABLE tree ADD cdsrc varchar(255) DEFAULT NULL",
            );


    foreach($sql_requests as $sql) {
        if ( ! $db->query($sql) ) { echo "Failed with this request :\n$sql\n"; exit; }
    }

# Update the CdSrc from datas to specific column
    $cdsrc_item = array();
    $db->prepare("SELECT * from tree where pwtype = 'container' AND datas <> 'a:0:{}' AND datas like '%CdSrc%'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $datas = unserialize($r['datas']);
        if(isset($datas['CdSrc'])) {
            $cdsrc_item[] = $r;
        }
    }
    $db->free();

    foreach ($cdsrc_item as $a) {
        if($debug_enabled) echo "  Updating item ".$a['id']." '".$a['title']."'\n";
        $a_datas = unserialize($a['datas']);

        if(isset($a_datas['CdSrc'])) {
            $cdsrc = $a_datas['CdSrc'];
            unset($a_datas['CdSrc']);
        }
        $db->prepare(
                "UPDATE tree SET datas = ?, cdsrc = ? WHERE id = ?",
                array('text', 'text' , 'integer')
                );

        $db->execute(array(
                    serialize($a_datas),
                    $cdsrc,
                    $a['id']
                    ));
        $db->free();
    }


# Update the schema version number
    $db->prepare("UPDATE config SET value = ? WHERE confkey = 'schema_version'", array('text'));
    $db->execute(array($schema_version_new));
    $db->free();
    $db->destroy();
    print "Done\n";
} else {
    print "Could not connect to database\n";
    exit;
}

?>
