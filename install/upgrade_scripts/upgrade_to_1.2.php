#! /usr/bin/php
<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et :

$schema_version_required = '1.1';
$schema_version_new = '1.2';


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
            );


    foreach($sql_requests as $sql) {
        if ( ! $db->query($sql) ) { echo "Failed with this request :\n$sql\n"; exit; }
    }

# Update datas
    $db->prepare("SELECT * from tree where pwtype = 'container' AND datas <> 'a:0:{}' AND datas like '%sort%'");
    $db->execute();
    while($db->nextr()) {
        $r = $db->get_row('assoc');
        $datas = unserialize($r['datas']);
        if(isset($datas['sort'])) {
            $cdsrc_item[] = $r;
        }
    }
    $db->free();

    foreach ($cdsrc_item as $a) {
        if($debug_enabled) echo "  Updating item ".$a['id']." '".$a['title']."'\n";
        $a_datas = unserialize($a['datas']);

        if(isset($a_datas['sort'])) {
            $a_datas['container_options']['sort'] = $a_datas['sort'];
            unset($a_datas['sort']);
        }
        $db->prepare(
                "UPDATE tree SET datas = ? WHERE id = ?",
                array('text', 'integer')
                );

        $db->execute(array(
                    serialize($a_datas),
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
