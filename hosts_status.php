<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/*
 * Copyright (C) 2014  Yves Mettier <ymettier at free.fr>
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

require 'lib/common.php';

$collectd_source = get_arg("collectd_source", 0, 0, "", __FILE__, __LINE__);
$include_aggregators = get_arg("include_aggregators", 0, 0, "", __FILE__, __LINE__);
$return_unknown_only = get_arg("return_unknown_only", 1, 0, "", __FILE__, __LINE__);

header('Content-Type: application/json');
header('Cache-Control: max-age=60');

$dead_servers_list = get_dead_servers_list($collectd_source, $include_aggregators, $return_unknown_only);
print json_encode($dead_servers_list);

exit();

?>
