<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/*
 *
 * Copy me to config.php and edit me.
 *
 */

/* Database configuration */
#$db_config = array(
#	"servername"=> "localhost",
#	"username"	=> "root",
#	"password"	=> "",
#	"database"	=> "perfwatcher"
#);

/** 
 *
 *	Custom widget. 
 *	Have a look at the wiki to sea how to create custom widget 
 *
 */
# $widgets[] = 'custom_widget';

/**
 *
 *	Peuplator plugin. 
 *	Have a look at the wiki to see how to create plugin to fill folder with you CMDB content 
 *
 */
# $peuplator_plugins[] = 'folder_filling_my_cmdb';

/**
 *
 *	Extra javascript file 
 *	Add here all javascript you want to add to the web interface 
 *
 */
# $extra_jsfile[] = 'js/custom_plugin_view.js';

/**
 *
 *	Collectd sources definitions
 *	Add/set your collectd sources
 *  Note : localhost as a server needs a Unix socket for rrdcached
 *
 */
$collectd_source_default = "localhost";
$collectd_sources = array(
    "localhost" => array( 'hostname' => "localhost", 'jsonrpc' => "http://127.0.0.1:8080/" ),
);


/**
 *
 *	JsonRPC server for RRD informations
 *	Default value http://127.0.0.1:8080/
 *
 */
# $jsonrpc_server = 'http://127.0.0.1:8080/';

/**
 *
 *	JsonRPC server for Top-ps informations
 *	Default value http://127.0.0.1:8080/
 *
 */
# $jsonrpc_topps_server = 'http://127.0.0.1:8080/';

/**
 *
 *	Top jsonRPC http proxy
 *	Default value null
 *
 */
# $jsonrpc_topps_httpproxy = 'http://10.0.0.1:3128/';

?>
