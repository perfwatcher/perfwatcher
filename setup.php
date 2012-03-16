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

$ok = "Your version of php is >= 5.3.0 (".PHP_VERSION.")";
$ko = "Your version of php is < 5.3.0 (".PHP_VERSION.")";
echo "<li>".(version_compare(PHP_VERSION, '5.3.0', '>=') ? printok ($ok) : printko ($ko))."</li><br/>";

$ok = "Your RRD module is present (".phpversion("rrd").")";
$ko = "Your RRD module is not present";
echo "<li>".(version_compare(phpversion("rrd"), '0.0.0', '>=') ? printok ($ok) : printko ($ko))."</li><br/>";

$ok = "rrdtool is present at $rrdtool";
$ko = "No rrdtool found at $rrdtool please install or modify \$rrdtool in etc/config.php";
echo "<li>".(isset($rrdtool) && file_exists($rrdtool) ? printok ($ok) : printko ($ko))."</li><br/>";

if (isset($rrdcached)) {
		$ok = "rrdcached socket is present at $rrdcached but make sure it writable with group of your webserver";
		$ko = "No rrdcached socket found at $rrdcached please install or modify \$rrdcached in etc/config.php and make sure it writable with group of your webserver";
		echo "<li>".(file_exists($rrdcached) ? printok ($ok) : printko ($ko))."</li><br/>";
}

$ok = "$rrds_path is present ";
$ko = "$rrds_path does not exists, check $rrds_path in etc/config.php and your collectd installation";
echo "<li>".(isset($rrds_path) && is_dir($rrds_path) ? printok ($ok) : printko ($ko))."</li><br/>";

$smarty_path = array('Smarty/Smarty.class.php', 'smarty/libs/Smarty.class.php');
foreach ($smarty_path as $path) {
    @include $path;
}
$ok = "Smarty PEAR module is present ";
$ko = "Smarty PEAR module is not present see <a href=\"INSTALL\">INSTALL</a>";
echo "<li>".(class_exists('Smarty') ? printok ($ok) : printko ($ko))."</li><br/>";

?>
		</ul>
    </body>
</html>
