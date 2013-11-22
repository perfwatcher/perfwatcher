<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
#
//header("Content-type: application/json");
$host = get_arg('host', 0, 0, "Error : No valid host found !!!", __FILE__, __LINE__);
$view_id = get_arg('view_id', 0, 1, "Error : No valid view_id found !!!", __FILE__, __LINE__);
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);
if(isset($collectd_source) && $collectd_source && isset($collectd_sources[$collectd_source])) {
    $url_jsonrpc = $collectd_sources[$collectd_source]['jsonrpc'];
} else {
    pw_error_log("This line should not be executed. Please tell us...",  __FILE__, __LINE__);
    $url_jsonrpc = $jsonrpc_server;
}

// ps-1335804082.gz
$time = isset($_GET['time']) ? $_GET['time'] : time();

// if (!$t1 || !$t2) { echo json_encode(array()); exit; }

$json1 = json_encode(array("jsonrpc" => "2.0","method" => "topps_get_top","params" => array("hostname" => $host,"tm" => (int)$time,"end_tm" => (int)$time - 60),"id" => 0));
$json2 = json_encode(array("jsonrpc" => "2.0","method" => "topps_get_top","params" => array("hostname" => $host,"tm" => (int)$time -60,"end_tm" => (int)$time - 120),"id" => 0));

putenv('http_proxy');
putenv('https_proxy');
$ch = curl_init($url_jsonrpc);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $jsonrpc_topps_httpproxy == null ? FALSE : TRUE);
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

//$data = array_slice($data, 63);
echo json_encode(array('data' => array_values($data), 'date1' => $t1, 'date2' => $t2));

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
