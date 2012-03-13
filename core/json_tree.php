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

$jstree = new json_tree();

switch($_REQUEST["operation"]) {
    case "create_node":
    case "remove_node":
    case "rename_node":
    case "move_node":
        if(!ereg("/admin/", $_SERVER["REQUEST_URI"])) { die(); }
    break;
    case "get_children":
    case "search":
    break;
}


if(isset($_REQUEST["operation"]) && $_REQUEST["operation"] && strpos("_", $_REQUEST["operation"]) !== 0 && method_exists($jstree, $_REQUEST["operation"])) {
	header("HTTP/1.0 200 OK");
	header('Content-type: text/json; charset=utf-8');
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	echo $jstree->{$_REQUEST["operation"]}($_REQUEST);
}

?>
