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

//print_r($_GET['node']);
require "lib/ofc/open-flash-chart.php";
$jstree = new json_tree();

$nodelist = $jstree->get_nodechildren_id($_GET['node']);
$status = get_status($nodelist);


$d = array();
$d[] = new pie_value(count($status['up']), "Up");
$d[] = new pie_value(count($status['down']), "Down");
$d[] = new pie_value(count($status['unknown']), "Unknown");

$pie = new pie();
$pie->set_animate( true );
//$pie->add_animation( new pie_fade() );
$pie->set_label_colour( '#432BAF' );
$pie->set_start_angle( 35 );
//$pie->set_alpha( 0.75 );
//
// This is where we turn of the labels,
// but we use them inside the tooltip:
//
$pie->set_tooltip( '#label#<br>#val# hosts (#percent#)' );
$pie->set_colours(array('#77CC6D', '#FF5973', '#838282'));

$pie->set_values( $d );
$pie->on_click('piesliceclicked');

$chart = new open_flash_chart();
$chart->set_bg_colour( '#FFFFFF' );
$chart->add_element( $pie );

echo $chart->toPrettyString();

?>
