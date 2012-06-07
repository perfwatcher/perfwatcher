<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en-US">
    <head profile="http://www.w3.org/2005/10/profile">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>PerfWatcher installation checking</title>
		<link rel="icon" type="image/ico" href="img/perfwatcher.ico">
	</head>
    <body>
		<ul>
<?php
require 'etc/config.default.php';
function printok($txt) { return "<font color='green'>$txt</font>"; }
function printko($txt) { return "<font color='red'>$txt</font>"; }
function printoo($txt) { return "<font color='orange'>$txt</font>"; }

$ok = "Your version of php is >= 5.3.0 (".PHP_VERSION.")";
$oo = "Your version of php is < 5.3.0 (".PHP_VERSION."), so no PHP RRD module, Perfwatcher will use rrdtool in command-line";
echo "<li>".(version_compare(PHP_VERSION, '5.3.0', '>=') ? printok ($ok) : printoo ($oo))."</li>";

$ok = "PHP RRD module is present (".phpversion("rrd").")";
$oo = "PHP RRD module is not present, Perfwatcher will use rrdtool in command-line";
echo "<li>".(version_compare(phpversion("rrd"), '0.0.0', '>=') ? printok ($ok) : printoo ($oo))."</li>";

$ok = "rrdtool is present at $rrdtool";
$ko = "No rrdtool found at $rrdtool please install or modify \$rrdtool in etc/config.php";
echo "<li>".(isset($rrdtool) && file_exists($rrdtool) ? printok ($ok) : printko ($ko))."</li>";

if (isset($rrdtool) && file_exists($rrdtool)) {
	$cmd = "$rrdtool | awk '{print $2; exit(0)}'";
	$rrdtool_version = trim(`$cmd`);
	$ok = "rrdtool version is great than 1.4.0 ($rrdtool_version)";
	$oo = "rrdtool version is less than 1.4.0 ($rrdtool_version). No rrdcached available and some feature will not work.";
	$ko = "Can't retrieve rrdtool version. Check your \$rrdtool in etc/config.php";
	if (!ereg("^[0-9]+(\.[0-9+])*$", $rrdtool_version)) {
		echo  "<li>".printko($ko)."</li>";
	} else if (version_compare($rrdtool_version, '1.4.0', '<')) {
		echo  "<li>".printoo($oo)."</li>";
	} else if (version_compare($rrdtool_version, '1.4.0', '>=')) {
		echo  "<li>".printok($ok)."</li>";
		$rrdcached_ok = true;
	} else {
		echo  "<li>".printko($ko)."</li>";
	}
	
}

if ($rrdcached_ok && isset($rrdcached)) {
		$ok = "rrdcached socket is present at $rrdcached but make sure it writable with group of your webserver";
		$ko = "No rrdcached socket found at $rrdcached please install or modify \$rrdcached in etc/config.php and make sure it writable with group of your webserver";
		echo "<li>".(file_exists($rrdcached) ? printok ($ok) : printko ($ko))."</li>";
}

if (isset($rrdtool) && file_exists($rrdtool)) {
	$cmd = "$rrdtool graph --border 1 2>&1";
	$res = trim(`$cmd`);
	if (substr($res, 0, 21) == 'ERROR: unknown option') {
		$options = $rrdtool_options;
		foreach ($options as $key => $val) {
			if ($val == '--border') {
				unset($options[$key]);
				unset($options[$key + 1]);
			}
		}
		$line = implode("', '", $options);
		echo "<li>".printko("Your rrdtool does not support --border option. Please add <i>\$rrdtool_options = array('$line');</i> in etc/config.php")."</li>";
	} else {
		echo "<li>".printok("Your rrdtool support --border option.")."</li>";
	}
}

$ok = "$rrds_path is present ";
$ko = "$rrds_path does not exists, check $rrds_path in etc/config.php and your collectd installation";
echo "<li>".(isset($rrds_path) && is_dir($rrds_path) ? printok ($ok) : printko ($ko))."</li>";

?>
		</ul>
    </body>
</html>
