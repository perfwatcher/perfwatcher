<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/*
 *
 * Copy me to config.php and edit me.
 *
 */

/* Database configuration */
#$db_config = array(
#    "servername"=> "localhost",
#    "username"    => "root",
#    "password"    => "",
#    "database"    => "perfwatcher"
#);

/** 
 *
 *    Custom widget. 
 *    Have a look at the wiki to see how to create custom widget 
 *
 */
# $widgets[] = 'custom_widget';

/**
 *
 *    Peuplator plugin. 
 *    Have a look at the wiki to see how to create plugin to fill folder with you CMDB content 
 *
 */
# $peuplator_plugins[] = 'folder_filling_my_cmdb';

/**
 *
 *    Extra javascript file 
 *    Add here all javascript you want to add to the web interface 
 *
 */
# $extra_jsfile[] = 'js/custom_plugin_view.js';

/**
 *
 *    Collectd sources definitions
 *    Add/set your collectd sources
 *  Note : localhost as a server needs a Unix socket for rrdcached
 *
 */
$collectd_source_default = "localhost";
$collectd_sources = array(
    "localhost" => array( 'hostname' => "localhost", 'jsonrpc' => "http://127.0.0.1:8080/", 'proxy' => null ),
);

/**
 *
 *    Path for config files for aggregators
 *    Note : if Collectd is running on remote servers, you are in charge of copying
 *  the config files to the Collectd servers (see basic_aggregator configuration)
 *
 */
$aggregator_config_dir = "/var/www/html/perfwatcher/private";

?>
