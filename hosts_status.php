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

header('Content-Type: application/json');
header('Cache-Control: max-age=60');

    $dead_servers_list = get_dead_servers_list();
    print json_encode($dead_servers_list);

    exit();

?>
