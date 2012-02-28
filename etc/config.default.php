<?php
/*
 *
 * DO NOT MODIFY, create and use etc/config.php to overide configuration
 *
 */

$db_config = array(
	"servername"=> "localhost",
	"username"	=> "root",
	"password"	=> "",
	"database"	=> "jsTree"
);

$collectd_db_config = array(
	'phptype'  => 'mysql',
	"hostspec"=> "localhost",
	"username"	=> "root",
	"password"	=> "",
	"database"	=> "collectd"
);

$rrds_path = "/var/lib/collectd";

$grouped_type = array('apache_bytes','apache_requests','apache_scoreboard','cache_ratio','cpu','cpug','cpufreq','df_complex','dns_opcode','conntrack','entropy','frequency','humidity','invocations','ipt_bytes','ipt_packets','irq','java_memory','memory','mysql_commands','mysql_handler','ping','power', 'ps_state', 'swap','table_size','tcp_connections','threads','total_requests','total_time_in_ms','users','wirkleistung','specs','swap_io', 'grid','nfs_procedure','panfs_procedure','panfs_procedure_time');

$derive_type = array('apache_bytes','apache_requests','ath_stat','cache_operation','cache_result','compression','connections','contextswitch','cpu','derive','disk_merged','disk_octets','disk_ops_complex','disk_ops','disk_time','dns_answer','dns_notify','dns_octets','dns_opcode','dns_qtype','dns_query','dns_question','dns_rcode','dns_reject','dns_request','dns_resolver','dns_response','dns_transfer','dns_update','dns_zops','fork_rate','context_switch_rate','fscache_stat','http_request_methods','http_requests','http_response_codes','if_collisions','if_dropped','if_errors','if_multicast','if_octets','if_packets','if_rx_errors','if_tx_errors','invocations','io_octets','io_packets','ipt_bytes','ipt_packets','irq','memcached_command','memcached_octets','memcached_ops','mysql_commands','mysql_handler','mysql_locks','mysql_log_position','mysql_octets','nfs_procedure','nginx_requests','node_octets','node_stat','operations','pg_blks','pg_n_tup_c','pg_scan','pg_xact','protocol_counter','ps_cputime','ps_disk_octets','ps_disk_ops','ps_pagefaults','serial_octets','swap_io','total_bytes','total_connections','total_operations','total_requests','total_sessions','total_threads','total_time_in_ms','total_values','virt_cpu_total','virt_vcpu','vmpage_action','vmpage_faults','vmpage_io');

$not_grouped_type = array('current');


$container_plugins = array();
$container_plugins['aggregator'] = array('title' => 'Aggregator', 'url' => 'index.php?tpl=container_plugins_aggregator');
$container_plugins['options']    = array('title' => 'Options', 'url' => 'index.php?tpl=container_plugins_options');
$container_plugins['servername'] = array('title' => 'Autofill folder with server name filtering', 'url' => 'index.php?tpl=container_plugins_servername');
$container_plugins['manuallist'] = array('title' => 'Autofill folder with manual server list', 'url' => 'index.php?tpl=container_plugins_manuallist');

if (file_exists("etc/config.php")) { include("etc/config.php"); } else { die('no etc/config.php'); }
?>
