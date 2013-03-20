<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 

if (isset($_GET['type_instance']) && $_GET['type_instance'] == '_') { unset($_GET['type_instance']); }
if (isset($_GET['plugin_instance']) && $_GET['plugin_instance'] == '_') { unset($_GET['plugin_instance']); }

require('etc/config.graph.php');
require('lib/functions.graph.php');
require('etc/definitions.php');
require 'lib/common.php';



$host     = read_var('host', $_GET, null);
$plugin   = read_var('plugin', $_GET, null);
$pinst    = read_var('plugin_instance', $_GET, '');
$type     = read_var('type', $_GET, '');
$tinst    = read_var('type_instance', $_GET, '');
$begin 	  = read_var('begin', $_GET, -86400);
$end      = read_var('end', $_GET, null);

$rrd_cmd = $logscale = $tinylegend = $timespan = null;
$opts  = array();
$pinst = $pinst == '_' ? null : $pinst;
$tinst = $tinst == '_' ? null : $tinst;
$pinst = strlen($pinst) == 0 ? null : $pinst;
$tinst = strlen($tinst) == 0 ? null : $tinst;
$all_tinst = collectd_list_types($host, $plugin, $pinst, $type);
load_graph_definitions($logscale, $tinylegend);
if (isset($MetaGraphDefs[$type])) {
    if ($type == '_') {
        $rrd_cmd = $MetaGraphDefs[$type]($host, $plugin, $pinst, $type, $all_tinst, $opts);
    } else {
        $rrd_cmd = $MetaGraphDefs[$type]($host, $plugin, $pinst, $type, $tinst, $opts);
    }
} else {
    if (isset($GraphDefs[$type])) {
        $rrd_cmd = collectd_draw_generic($timespan, $host, $plugin, $pinst, $type, $tinst);
    } else {
        $rrd_cmd = collectd_draw_rrd($host, $plugin, $pinst, $type, $tinst);
    }
}

$prev_arg = $title = $vlabel = null;
$dss = array();
foreach($rrd_cmd as $arg) {
    if ($prev_arg == '-v') {
        $vlabel = $arg;
    } else if ($prev_arg == '-t') {
        $title = $arg;
    } else if (substr($arg, 0, 4) == 'DEF:') {
        list(,$d) = split('=', $arg);
        list($file, $ds,) = split(':', $d);
        if ($ds == 'value') {
            $tmp = split('/', $file);
            list(, $label) = split('-', substr($tmp[count($tmp)-1], 0, -4));
        } else { $label = $ds; }
        $dss[] = array('ds' => $ds, 'file' => $file, 'label' => $label);
    }

    $prev_arg = $arg;
}
$rrd_cmd = array();
$rrd_cmd[] = '--step';
$rrd_cmd[] = 60;
$rrd_cmd[] = '-s';
$rrd_cmd[] = $begin;
if ($end) {
    $rrd_cmd[] = '-e';
    $rrd_cmd[] = $end;
}
if (isset($rrdcached) && file_exists($rrdcached)) {
    $rrd_cmd[] = "--daemon";
    $rrd_cmd[] = $rrdcached;
}

foreach ($dss as $key => $ds) {
    $rrd_cmd[] = 'DEF:var'.$key.'='.$ds['file'].':'.$ds['ds'].':AVERAGE';
    $rrd_cmd[] = 'XPORT:var'.$key.':'.$ds['label'];
}

$out = rrd_xport($rrd_cmd);

/*
   Array
   (
   [start] => 1341226140
   [end] => 1341229740
   [step] => 60
   [data] => Array
   (
   [0] => Array
   (
   [legend] => tx
   [data] => Array
   (
   [1341226140] => 2435.9266666667
   [1341226200] => 2417.2925
 */

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"graph.csv\";" );
echo "timestamp\tdate (y/m/d h:m:s)";
foreach ($out['data'] as $id => $data) {
    echo "\t".$data['legend'];
}
echo "\n";
foreach ($out['data'][0]['data'] as $time => $val) {
    echo $time."\t".date("Y/m/d H:i:s", $time);
    foreach ($out['data'] as $id => $data) {
        echo "\t".$data['data'][$time];
    }
    echo "\n";
}
?>
