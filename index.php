<?php
/**
 * Main dispatcher script
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

require 'lib/common.php';

if (!include 'Smarty/Smarty.class.php' ) {
    die("Error : Smarty PEAR module not present.<br />
Please install it.<br/>
Ex:<br>
- yum --nogpgcheck install php-Smarty.noarch<br>
- aptitude install Smarty<br>
- wget http://www.smarty.net/files/Smarty-3.0.7.tar.gz<br>
");
}

if (!isset($_GET['tpl']) && !isset($_POST['tpl'])) {
    $tplfile = 'index';
} elseif (isset($_GET['tpl'])) {
    $tplfile = $_GET['tpl']; 
} else {
    $tplfile = $_POST['tpl']; 
}

if (!file_exists("tpl/$tplfile.html")) {
    die("Error : Template tpl/$tplfile.html does not exists !");
}

$tpl = new Smarty();
$tpl->template_dir = 'tpl';
$tpl->compile_dir = '/dev/shm';
$tpl->php_handling = SMARTY_PHP_ALLOW;

if (file_exists("core/$tplfile.php")) {
    include "core/$tplfile.php";
}

$tpl->display("$tplfile.html");
?>
