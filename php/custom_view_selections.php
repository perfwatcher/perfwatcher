<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2011 Cyril Feraudet
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
 * @copyright 2011 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

header("HTTP/1.0 200 OK");
header('Content-type: text/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

$selection_id = get_arg('selection_id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);

$action_need_jstree = 0;
switch ($action) {
    case 'load_tab':
        echo json_encode(selection_get_data($selection_id));
        break;
    case 'save_markup':
        $markup = get_arg('markup', "", 0, "Error : No valid markup found !!!", __FILE__, __LINE__);
        selection_update_markup($selection_id, $markup);
        echo json_encode(array());
        break;
    default:
        $action_need_jstree = 1;
}


if($action_need_jstree) {
    
    $id = get_arg('id', 0, 1, "Error : No valid id found !!!", __FILE__, __LINE__);
    $view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
    
    $jstree = new json_tree($view_id);
    $res = $jstree->_get_node($id);
    $datas = $jstree->get_datas($res['id']);
    
#    if (isset($_POST['action'])) {
#        switch ($_POST['action']) {
#            case 'some_action':
#                break;
#        }
#    }
}

?>
