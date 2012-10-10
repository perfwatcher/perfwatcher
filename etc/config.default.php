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

$rrds_path = "/var/lib/collectd";
$notification_path = "/var/lib/collectd/_notification";

$grouped_type = array('apache_bytes','apache_requests','apache_scoreboard','cache_ratio','cpu','cpug','cpufreq','df_complex','dns_opcode','conntrack','entropy','frequency','humidity','invocations','ipt_bytes','ipt_packets','irq','java_memory','memory','mysql_commands','mysql_handler','ping', 'ps_state', 'swap','table_size','tcp_connections','threads','total_requests','total_time_in_ms','users','wirkleistung','specs','swap_io', 'grid','nfs_procedure','panfs_procedure','panfs_procedure_time');

$derive_type = array('apache_bytes','apache_requests','ath_stat','cache_operation','cache_result','compression','connections','contextswitch','cpu','derive','disk_merged','disk_octets','disk_ops_complex','disk_ops','disk_time','dns_answer','dns_notify','dns_octets','dns_opcode','dns_qtype','dns_query','dns_question','dns_rcode','dns_reject','dns_request','dns_resolver','dns_response','dns_transfer','dns_update','dns_zops','fork_rate','context_switch_rate','fscache_stat','http_request_methods','http_requests','http_response_codes','if_collisions','if_dropped','if_errors','if_multicast','if_octets','if_packets','if_rx_errors','if_tx_errors','invocations','io_octets','io_packets','ipt_bytes','ipt_packets','irq','memcached_command','memcached_octets','memcached_ops','mysql_commands','mysql_handler','mysql_locks','mysql_log_position','mysql_octets','nfs_procedure','nginx_requests','node_octets','node_stat','operations','pg_blks','pg_n_tup_c','pg_scan','pg_xact','protocol_counter','ps_cputime','ps_disk_octets','ps_disk_ops','ps_pagefaults','serial_octets','swap_io','total_bytes','total_connections','total_operations','total_requests','total_sessions','total_threads','total_time_in_ms','total_values','virt_cpu_total','virt_vcpu','vmpage_action','vmpage_faults','vmpage_io');

$not_grouped_type = array('current');

$blacklisted_type = array('hyperthreading', 'nbcpu');

$rrdtool = '/usr/bin/rrdtool';

$rrdtool_options = array('--border', '0', '-c', 'BACK#FFFFFF', '-v', ' ');

$collectd_socket  = '/var/run/collectd-unixsock';

$widgets = array();
$widgets[] = 'vu_cpu_memory';
$widgets[] = 'folder_status';
$widgets[] = 'load_meter';
$widgets[] = 'folder_aggregator';
$widgets[] = 'folder_options';
$widgets[] = 'folder_filling_regex';
$widgets[] = 'folder_filling_manual';

$peuplator_plugins = array();
$peuplator_plugins[] = 'folder_filling_regex';
$peuplator_plugins[] = 'folder_filling_manual';

$extra_jsfile = array();
$extra_jsfile[] = 'js/custom_plugin_view.js';

if (file_exists("etc/config.php")) { include("etc/config.php"); } else { die('no etc/config.php please create it with at least : <br />&lt;?php<br/><br />?&gt;'); }

?>
