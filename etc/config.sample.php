<?php
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
 *	Directory where Collectd write RRD file
 *
 */
# $rrds_path = "/var/lib/collectd/rrd";

/**
 *
 *	Directory where Collectd write notification through notify_file plugin
 *
 */
# $notification_path = "/var/lib/collectd/_notification";

/**
 *
 *	Path to rrdtool binary
 *
 */
# $rrdtool = '/usr/bin/rrdtool';

/**
 *
 *	rrdcached socket (if used)
 *
 */
# $rrdcached = "/var/run/rrdcached/rrdcached.sock";

/** 
 *
 *	Path to Collectd unix socket (see unixsock plugin) 
 *
 */
# $collectd_socket  = '/var/run/collectd-unixsock';

/** 
 *
 *	Hostname used by aggregator. If not set, hostname from posix_uname() is used
 *
 */
# $aggregator_hostname  = 'my_custom_hostname';

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
 *	Top jsonRPC server
 *	Default value http://127.0.0.1:8080/
 *
 */
# $jsonrpc_topps_path = '/var/lib/collectd/top';

/**
 *
 *	Top jsonRPC server
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
