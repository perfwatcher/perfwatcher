<?php
if (!isset($_GET['host']) || trim($_GET['host']) == "") { die('No host specified'); }
if (isset($_GET['sort'])) { $sort = $_GET['sort']; } else { $sort = 'ctime'; }
$timestamp = isset($_GET['timestamp']) && is_numeric($_GET['timestamp']) ? $_GET['timestamp'] : time();
$tpl->assign('host', $_GET['host']);
$tpl->assign('timestamp', $timestamp);
$tpl->assign('sort', $sort);
$tpl->assign('date', date("Y/m/d H:i", $timestamp));
connect_dbcollectd();
$query = "SELECT message  FROM notification_view 
WHERE date < FROM_UNIXTIME($timestamp)
AND date > FROM_UNIXTIME($timestamp - 200)
AND hostname = '".$_GET['host']."' 
AND plugin = 'top' AND type = 'ps' LIMIT 2";
$res = $dbcollectd->query($query);
if (!$data1 = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) { $data1 = array('message' => ''); }
if (!$data2 = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) { $data2 = array('message' => ''); }
$data1 = trim($data1['message']);
$data2 = trim($data2['message']);

$data1 = get_ps_hash($data1);
$data2 = get_ps_hash($data2);
$data = array_intersect_uassoc($data1, $data2, "strcmp");
calc_time_derive($data, $data2);
$datacpy = $data;
switch($sort) {
    case 'user':
    case 'group':
    case 'rss':
    case 'ctime':
    case 'process':
        uksort($data, 'custom_sort');
    break;
    default:
        ksort($data);
    break;
}

$tpl->assign('datas', $data);


function custom_sort($a, $b) {
    global $sort, $datacpy;
    if (!is_numeric($datacpy[$a][$sort]) && !is_numeric($datacpy[$b][$sort])) {
        return strcasecmp($datacpy[$a][$sort], $datacpy[$b][$sort]);
    } else {
        return $datacpy[$a][$sort] === $datacpy[$b][$sort] ? 0 : ($datacpy[$a][$sort] > $datacpy[$b][$sort] ? -1 : 1);
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
    foreach ($data as $key => $val) {
        $data[$key]['stime'] = $data2[$key]['stime'] - $data[$key]['stime'];
        $data[$key]['utime'] = $data2[$key]['utime'] - $data[$key]['utime'];
        $data[$key]['ctime'] = ceil(($data[$key]['utime'] + $data[$key]['stime']) / 100);
        $data[$key]['rss'] *= 4096;
    }
}



?>
