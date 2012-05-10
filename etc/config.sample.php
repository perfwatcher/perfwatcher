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
#	"database"	=> "jsTree"
#);

#$collectd_db_config = array(
#	'phptype'  => 'mysql',
#	"hostspec"=> "localhost",
#	"username"	=> "root",
#	"password"	=> "",
#	"database"	=> "collectd"
#);

/**
 *
 *	Directory where Collectd write RRD file
 *
 */
#$rrds_path = "/var/lib/collectd";

/**
 *
 *	Directory where Collectd write notification through notify_file plugin
 *
 */
#$notification_path = "/var/lib/collectd/_notification";

/**
 *
 *	Path to rrdtool binary
 *
 */
#$rrdtool = '/usr/bin/rrdtool';

/** 
 *
 *	Path to Collectd unix socket (see unixsock plugin) 
 *
 */
# $collectd_socket  = '/var/run/collectd-unixsock';

/** 
 *
 *	Custom widget. 
 *	Have a look to the wiki to sea how to create custom widget 
 *
 */
# $widgets[] = 'custom_widget';

/**
 *
 *	Peuplator plugin. 
 *	Have a look to the wiki to sea how to create plugin to fill folder with you CMDB content 
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


?>
