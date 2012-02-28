<?php
/**
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2011 Cyril Feraudet
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */ 


header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

if (!isset($_GET['host']) and !isset($_POST['host'])) {
    die('Error : POST or GET host missing !!');
}

if (isset($_GET['host'])) {
    $host = $_GET['host'];
} elseif (isset($_POST['host']) && is_numeric($_POST['host'])) {
    $host = $_POST['host'];
}

$tpl->assign('timestamp', time());
$tpl->assign('host', $host);

$tpl->assign('timeyesterday',time() - 86400);
$tpl->assign('time',time());

if (is_dir($rrds_path.'/'.$host)) {
    $plugins = load_datas($host);
    $tpl->assign('plugins', $plugins);
} else {
    $tpl->assign('plugins', array());
}
//echo '<pre>'.print_r($plugins, true).'</pre>';

?>
