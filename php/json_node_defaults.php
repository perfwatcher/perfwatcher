<?php # vim: set filetype=php fdm=marker sw=4 ts=4 tw=78 et : 
/**
 * Copyright (c) 2013 Yves Mettier
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
 * @author    Yves Mettier <ymettier AT free fr>
 * @copyright 2013 Yves Mettier
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

header("HTTP/1.0 200 OK");
header('Content-type: text/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

if (isset($_GET['host'])) {
    $host = $_GET['host'];
} elseif (isset($_POST['host'])) {
    $host = $_POST['host'];
} else {
    die('Error : No valid name found !!!');
}

$collectd_source_list = array();
if (isset($_GET['CdSrc']) && $_GET['CdSrc']) {
    $collectd_source_list[] = $_GET['CdSrc'];
} elseif (isset($_POST['CdSrc']) && $_POST['CdSrc']) {
    $collectd_source_list[] = $_POST['CdSrc'];
} else {
    $collectd_source_list = array_keys($collectd_sources);
}

$collectd_source = "";
foreach ($collectd_source_list as $cs) {
    $plugins = get_list_of_rrds($cs, $host);
    if(! empty($plugins)) {
        $collectd_source = $cs;
        break;
    }
}

if("" == $collectd_source) {
    echo json_encode(array());
} else {
    $item_datas = new json_item_data();
    $item_datas->set_host($host);
    $item_datas->set_plugins($plugins);
    $item_datas->set_jstree(array('title' => $host, 'pwtype' => 'server'));
    $item_datas->set_config_widgets(get_widget(array( 'pwtype' => 'server' )));
    $item_datas->set_config_source("", $collectd_source, 2);
    echo $item_datas->to_json();
}
?>

