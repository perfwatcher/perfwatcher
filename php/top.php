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
#
//header("Content-type: application/json");
$host = get_arg('host', 0, 0, "Error : No valid host found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);
if(isset($collectd_source) && $collectd_source && isset($collectd_sources[$collectd_source])) {
    $url_jsonrpc = $collectd_sources[$collectd_source]['jsonrpc'];
    $proxy_jsonrpc = isset($collectd_sources[$collectd_source]['proxy'])?$collectd_sources[$collectd_source]['proxy']:null;
} else {
    pw_error_log("This line should not be executed. Please tell us...",  __FILE__, __LINE__);
    $url_jsonrpc = "http://127.0.0.1:8080/";
    $proxy_jsonrpc = null;
}

// ps-1335804082.gz
$time = isset($_GET['time']) ? $_GET['time'] : time();
$sortname = isset($_GET['sidx']) ? $_GET['sidx'] : "cpu";
$sortorder = isset($_GET['sord']) ? $_GET['sord'] : "asc";

// if (!$t1 || !$t2) { echo json_encode(array()); exit; }

$json1 = json_encode(array("jsonrpc" => "2.0","method" => "topps_get_top","params" => array("hostname" => $host,"tm" => (int)$time,"end_tm" => (int)$time - 60),"id" => 0));
$json2 = json_encode(array("jsonrpc" => "2.0","method" => "topps_get_top","params" => array("hostname" => $host,"tm" => (int)$time -60,"end_tm" => (int)$time - 120),"id" => 0));

putenv('http_proxy');
putenv('https_proxy');
$ch = curl_init($url_jsonrpc);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_jsonrpc == null ? FALSE : TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json1))
        );
if($result = curl_exec($ch)) {
    if ($result  != '' && $result = json_decode($result)) {
        if (isset($result->result->topps)) {
            $data1 = implode("", $result->result->topps);
        } else {
            exit_error($result);
        }
    } else {
        exit_error(array("result not json :" => $result));
    }
} else {
    exit_error(curl_error($ch));
}
$t1 = $result->result->tm;
curl_setopt($ch, CURLOPT_POSTFIELDS, $json2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json2))
        );
if($result = curl_exec($ch)) {
    if ($result  != '' && $result = json_decode($result)) {
        if (isset($result->result->topps)) {
            $data2 = implode("", $result->result->topps);
        } else {
            exit_error($result);
        }
    } else {
        exit_error(array("result not json :" => $result));
    }
} else {
    exit_error(curl_error($ch));
}
$t2 = $result->result->tm;
$data1 = get_ps_hash($data1);
$data2 = get_ps_hash($data2);
$data = array_intersect_uassoc($data1, $data2, "strcmp");
calc_time_derive($data, $data2);
uasort($data, 'ps_hash_sort');

//$data = array_slice($data, 63);
echo json_encode(array(
            'rows' => array_values($data), 
            'userdata' => array('date1' => $t1, 'date2' => $t2)
            )
        );

function ps_hash_sort($a,$b) {
    global $sortname, $sortorder;
    if($a[$sortname] == $b[$sortname]) return(0);
    if($sortorder == 'asc') {
        return ($a[$sortname] > $b[$sortname]) ? 1 : -1;
    } else {
        return ($a[$sortname] > $b[$sortname]) ? -1 : 1;
    }
}

function get_ps_hash($data) {
    $ret = array();
    $data = trim($data);
    foreach(split("\n", $data) as $line) {
        $pid = $ppid = $uid = $user = $gid = $group = $rss = $stime = $utime = $process = false;
        $chars = count_chars($line);
        if ($chars[32] > 8) {
            list($pid, $ppid, $uid, $user, $gid, $group, $rss, $stime, $utime, $process) = split(' ', $line, 10);
            if ($process !== false && $ppid != 2) {
                $ret[$pid] = array('ppid' => $ppid, 'uid' => $uid, 'user' => $user, 'gid' => $gid, 'group' => $group,
                        'rss' => $rss, 'stime' => $stime, 'utime' => $utime, 'process' => $process);
            }
        }
    }

    return $ret;
}

function calc_time_derive(&$data, $data2) {
    global $t1, $t2;
    foreach ($data as $key => $val) {
        $data[$key]['pid'] = $key;
        $data[$key]['userlabel'] = $data[$key]['user'];
        $data[$key]['grouplabel'] = $data[$key]['group'];
        $data[$key]['stime'] = $data2[$key]['stime'] - $data[$key]['stime'];
        $data[$key]['utime'] = $data2[$key]['utime'] - $data[$key]['utime'];
        $data[$key]['cpu'] = ceil(($data[$key]['utime'] + $data[$key]['stime'])/($t2 - $t1));
        $data[$key]['rss'] *= 4096;
    }
}

function exit_error($error) {
    echo json_encode(array("error" => $error, "data" => array())); exit;
}
?>
