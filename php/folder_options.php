<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2012 Cyril Feraudet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2012 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

require 'lib/class.folder_options.php';

global $collectd_sources;

if (!isset($_GET['action']) and !isset($_POST['action'])) {
    die('Error : POST or GET action missing !!');
}

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

$action_need_jstree = 0;
switch ($action) {
    case "get_config_list":
        echo json_encode(array_keys($collectd_sources));
        break;

    default:
        $action_need_jstree = 1;
}

if($action_need_jstree) {
    $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
    $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);

    $jstree = new json_tree($view_id);
    $res = $jstree->_get_node($id);
    $owidget = new folder_options($res);
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {

            case "save_sort":
                $sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_POST['sort']) ? $_POST['sort'] : '');
                if ($owidget->save_sort($sort)) {
                    die('Sort method saved.');
                }
                break;

            case "save_cdsrc":
                $src = isset($_GET['src']) ? $_GET['src'] : (isset($_POST['src']) ? $_POST['src'] : '');
                if ($owidget->save_cdsrc($src)) {
                    die('Conditions saved. You can now refresh the tree');
                }
                break;

            default:
                die('No valid action submited !');
                break;

        }
    }
}
?>
