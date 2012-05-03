<?php // vim:fenc=utf-8:filetype=php:ts=4
/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (isset($_GET['blank']))  { exit(); }

//error_reporting(E_ALL | E_NOTICE | E_WARNING);

if (isset($_GET['type_instance']) && $_GET['type_instance'] == '_') { unset($_GET['type_instance']); }
if (isset($_GET['plugin_instance']) && $_GET['plugin_instance'] == '_') { unset($_GET['plugin_instance']); }
require('etc/config.graph.php');
require('lib/functions.graph.php');
require('etc/definitions.php');
require 'lib/common.php';

function makeTextBlock($text, $fontfile, $fontsize, $width) {
	// TODO: handle explicit line-break!
	$words = explode(' ', $text);
	$lines = array($words[0]);
	$currentLine = 0;
    array_shift($words);
	foreach ($words as $word) {
		$lineSize = imagettfbbox($fontsize, 0, $fontfile, $lines[$currentLine] . ' ' . $word);
		if($lineSize[2] - $lineSize[0] < $width) {
			$lines[$currentLine] .= ' ' . $word;
		} else {
			$currentLine++;
			$lines[$currentLine] = $word;
		}
	}
	//error_log(sprintf('Handles message "%s", %d words => %d/%d lines', $text, count($words), $currentLine, count($lines)));
	return implode("\n", $lines);
}

/**
 * No RRD files found that could match request
 * @code HTTP error code
 * @code_msg Short text description of HTTP error code
 * @title Title for fake RRD graph
 * @msg Complete error message to display in place of graph content
 */
function error($code, $code_msg, $title, $msg) {
	global $config;
	header(sprintf("HTTP/1.0 %d %s", $code, $code_msg));
	header("Pragma: no-cache");
	header("Expires: Mon, 01 Jan 2008 00:00:00 CET");
	header("Content-Type: image/png");
	$w = $config['rrd_width']+81+16;
	$h = $config['rrd_height']+79;

	$png     = imagecreate($w, $h);
	
	$c_bkgnd = imagecolorallocate($png, 255, 255, 255);
	if (isset($_GET['debug'])) {
			$c_fgnd  = imagecolorallocate($png, 255, 255, 255);
			$c_blt   = imagecolorallocate($png, 208, 208, 208);
			$c_brb   = imagecolorallocate($png, 160, 160, 160);
			$c_grln  = imagecolorallocate($png, 114, 114, 114);
			$c_grarr = imagecolorallocate($png, 128,  32,  32);
			$c_txt   = imagecolorallocate($png, 0, 0, 0);
			$c_etxt  = imagecolorallocate($png, 64, 0, 0);
			if (function_exists('imageantialias'))
				imageantialias($png, true);
			//imagefilledrectangle($png, 0,   0, $w, $h, $c_bkgnd);
			imagefilledrectangle($png, 51, 33, $w-31, $h-47, $c_fgnd);
			imageline($png,  51,  30,  51, $h-43, $c_grln);
			imageline($png,  48, $h-46, $w-28, $h-46, $c_grln);
			imagefilledpolygon($png, array(49, 30,    51, 26,    53, 30), 3, $c_grarr);
			imagefilledpolygon($png, array($w-28, $h-48,  $w-24, $h-46,  $w-28, $h-44), 3, $c_grarr);
		//	imageline($png,    0,    0,   $w,    0, $c_blt);
		//	imageline($png,    0,    1,   $w,    1, $c_blt);
		//	imageline($png,    0,    0,    0,   $h, $c_blt);
		//	imageline($png,    1,    0,    1,   $h, $c_blt);
		//	imageline($png, $w-1,    0, $w-1,   $h, $c_brb);
		//	imageline($png, $w-2,    1, $w-2,   $h, $c_brb);
		//	imageline($png,    1, $h-2,   $w, $h-2, $c_brb);
		//	imageline($png,    0, $h-1,   $w, $h-1, $c_brb);
			imagestring($png, 4, ceil(($w-strlen($title)*imagefontwidth(4)) / 2), 10, $title, $c_txt);
			imagestring($png, 5, 60, 35, sprintf('%s [%d]', $code_msg, $code), $c_etxt);
			if (function_exists('imagettfbbox') && is_file($config['error_font'])) {
				// Detailled error message
				$fmt_msg = makeTextBlock($msg, $config['error_font'], 10, $w-86);
				$fmtbox  = imagettfbbox(12, 0, $config['error_font'], $fmt_msg);
				imagettftext($png, 10, 0, 55, 35+3+imagefontwidth(5)-$fmtbox[7]+$fmtbox[1], $c_txt, $config['error_font'], $fmt_msg);
			} else {
				imagestring($png, 4, 53, 35+6+imagefontwidth(5), $msg, $c_txt);
			}
	}
	imagepng($png);
	imagedestroy($png);
}

/**
 * No RRD files found that could match request
 */
function error404($title, $msg) {
	return error(404, "Not found", $title, $msg);
}

/**
 * Incomplete / invalid request
 */
function error400($title, $msg) {
	return error(400, "Bad request", $title, $msg);
}

/**
 * Incomplete / invalid request
 */
function error500($title, $msg) {
	return error(500, "Internal error", $title, $msg);
}

// Process input arguments
$host     = read_var('host', $_GET, null);
if (is_null($host))
	return error400("?/?-?/?", "Missing host name");
else if (!is_string($host))
	return error400("?/?-?/?", "Expecting exactly 1 host name");
else if (strlen($host) == 0)
	return error400("?/?-?/?", "Host name may not be blank");

$plugin   = read_var('plugin', $_GET, null);
if (is_null($plugin))
	return error400($host.'/?-?/?', "Missing plugin name");
else if (!is_string($plugin))
	return error400($host.'/?-?/?', "Plugin name must be a string");
else if (strlen($plugin) == 0)
	return error400($host.'/?-?/?', "Plugin name may not be blank");

$pinst    = read_var('plugin_instance', $_GET, '');
if (!is_string($pinst))
	return error400($host.'/'.$plugin.'-?/?', "Plugin instance name must be a string");

$type     = read_var('type', $_GET, '');
if (is_null($type))
	return error400($host.'/'.$plugin.(strlen($pinst) ? '-'.$pinst : '').'/?', "Missing type name");
else if (!is_string($type))
	return error400($host.'/'.$plugin.(strlen($pinst) ? '-'.$pinst : '').'/?', "Type name must be a string");
else if (strlen($type) == 0)
	return error400($host.'/'.$plugin.(strlen($pinst) ? '-'.$pinst : '').'/?', "Type name may not be blank");

$tinst    = read_var('type_instance', $_GET, '');

$graph_identifier = $host.'/'.$plugin.(strlen($pinst) ? '-'.$pinst : '').'/'.$type.(strlen($tinst) ? '-'.$tinst : '-*');

$timespan = read_var('timespan', $_GET, $config['timespan'][0]['name']);
$timespan_ok = false;
foreach ($config['timespan'] as &$ts)
	if ($ts['name'] == $timespan)
		$timespan_ok = true;
if (!$timespan_ok)
	return error400($graph_identifier, "Unknown timespan requested");

$_GET['begin'] = read_var('begin', $_GET, '-86400');
$_GET['end'] = read_var('end', $_GET, '');
$_GET['begin'] = read_var('graph_start', $_GET, $_GET['begin']);
$_GET['end'] = read_var('graph_end', $_GET, $_GET['end']);

$xcenter = $_GET['begin'] < 0 ? $xcenter = time() + ($_GET['begin'] / 2) : $xcenter = $_GET['end'] - (($_GET['end'] - $_GET['begin']) / 2);
$xcenter = intval($xcenter);

$xconfig = array();
if(is_numeric($_GET['end']) and $_GET['begin'] > 0 and  $_GET['end'] - $_GET['begin'] > 2160000 and $_GET['end'] - $_GET['begin'] < 12960000)
{
        if($_GET['end'] - $_GET['begin'] < 12960000 - (86400*7))
                $xconfig = array('--x-grid', 'DAY:1:DAY:7:DAY:7:0:%d/%m');
        else
                $xconfig = array('--x-grid', 'DAY:1:DAY:7:DAY:14:0:%d/%m');
}

$logscale   = (boolean)read_var('logarithmic', $_GET, false);
$tinylegend = (boolean)read_var('tinylegend', $_GET, false);

// Check that at least 1 RRD exists for the specified request
$all_tinst = collectd_list_types($host, $plugin, $pinst, $type);
if (count($all_tinst) == 0)
	return error404($graph_identifier, "No rrd file found for graphing : $host, $plugin, $pinst, $type");

// Now that we are read, do the bulk work
load_graph_definitions($logscale, $tinylegend);

$pinst = strlen($pinst) == 0 ? null : $pinst;
$tinst = strlen($tinst) == 0 ? null : $tinst;

$opts  = array();
$opts['timespan'] = $timespan;
if ($logscale)
	$opts['logarithmic'] = 1;
if ($tinylegend)
	$opts['tinylegend']  = 1;

$rrd_cmd = false;
if (isset($MetaGraphDefs[$type])) {
/*
	$identifiers = array();
	foreach ($all_tinst as &$atinst)
		$identifiers[] = collectd_identifier($host, $plugin, is_null($pinst) ? '' : $pinst, $type, $atinst);
	collectd_flush($identifiers);
*/
	if ($type == '_') {
		$rrd_cmd = $MetaGraphDefs[$type]($host, $plugin, $pinst, $type, $all_tinst, $opts);
	} else {
		$rrd_cmd = $MetaGraphDefs[$type]($host, $plugin, $pinst, $type, $tinst, $opts);
	}
} else {
//	collectd_flush(collectd_identifier($host, $plugin, is_null($pinst) ? '' : $pinst, $type, is_null($tinst) ? '' : $tinst));
	if (isset($GraphDefs[$type]))
		$rrd_cmd = collectd_draw_generic($timespan, $host, $plugin, $pinst, $type, $tinst);
	else
		$rrd_cmd = collectd_draw_rrd($host, $plugin, $pinst, $type, $tinst);
}
$rrd_cmd[] = 'VRULE:'.$GLOBALS['xcenter'].'#888888:'.date("Y/m/d H\\\\:i\\\\:s",$GLOBALS['xcenter']).'\l:dashes';
$rrd_cmd = array_merge($rrd_cmd,$xconfig);
$rrd_cmd[] = "-m";
$rrd_cmd[] = "1";
if (isset($rrdcached) && file_exists($rrdcached)) {
	$rrd_cmd[] = "--daemon";
	$rrd_cmd[] = $rrdcached;
}
if (isset($_GET['debug'])) {
	header('Content-Type: text/plain; charset=utf-8');
	printf("Would have executed:\n%s\n", is_array($rrd_cmd) ? "'$rrdtool' 'graph' '-' '".implode("' '", $rrd_cmd )."'" : $rrd_cmd);
	return 0;
} else if ($rrd_cmd) {
	header('Content-Type: image/png');
	header('Cache-Control: max-age=60');
	if (version_compare(phpversion("rrd"), '0.0.0', '>=')) {
    	$tmpfile = tempnam('/dev/shm/','rrd-phpcollectd-');
    	$ret = rrd_graph($tmpfile, $rrd_cmd);
    	readfile($tmpfile);
    	unlink($tmpfile);
	} else {
		$cmd = "'$rrdtool' 'graph' '-' '".implode("' '", $rrd_cmd )."'";
		echo `$cmd`;
	}
    exit();
	if (!is_array($ret))
		return error500($graph_identifier, "RRD failed to generate the graph");
} else {
	return error500($graph_identifier, "Failed to tell RRD how to generate the graph");
}

?>
