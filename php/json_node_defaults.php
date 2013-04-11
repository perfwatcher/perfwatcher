<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 
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
 * @author    Yves Mettier <ymettier@free.fr>
 * @copyright 2013 Yves Mettier
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.perfwatcher.org/
 */ 

header("HTTP/1.0 200 OK");
header('Content-type: text/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

if (!isset($_GET['host']) and !isset($_POST['host'])) {
    die('Error : POST or GET id missing !!');
}

if (isset($_GET['host'])) {
    $host = $_GET['host'];
} elseif (isset($_POST['host'])) {
    $host = $_POST['host'];
} else {
    die('Error : No valid name found !!!');
}

$plugins = load_datas($host);

echo json_encode(
        array(
            'host' => $host,
            'plugins' => $plugins,
            'jstree' => array('title' => $host),
            'datas' => array(),
            'config' => array(
                'widgets' => get_widget(array( 'type' => 'default' ) )
                )
            ));
?>

