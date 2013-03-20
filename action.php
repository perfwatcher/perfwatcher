<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 
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

if (!isset($_GET['tpl']) && !isset($_POST['tpl'])) {
    $tplfile = 'index';
} elseif (isset($_GET['tpl'])) {
    $tplfile = $_GET['tpl']; 
} else {
    $tplfile = $_POST['tpl']; 
}

if (!eregi("^[a-z0-9_\-]+$", $tplfile)) { $tplfile = 'index'; }

if (file_exists("php/$tplfile.php")) {
    include "php/$tplfile.php";
}
?>
