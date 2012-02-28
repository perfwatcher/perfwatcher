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

if (!isset($_GET['server'])) { die("No server specified !\n"); }
$oscdb = new _database;
$oscdb->settings = array_merge($oscdb->settings, $ocsdb_config);
if (!$oscdb->connect()) { die("Can't connect to OCS database\n"); }
$oscdb->query("SELECT * FROM hardware WHERE NAME LIKE '".$oscdb->escape($_GET['server'])."'");
$oscdb->nextr();
$tpl->assign('ocs',$oscdb->get_row('assoc'));
$oscdb->destroy();
?>
