<?php
//header("Content-type: application/json");
if (!isset($_GET['id']) and !isset($_POST['id'])) {
    die('Error : POST or GET id missing !!');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
} else {
    die('Error : No valid id found !!!');
}

$jstree = new json_tree();
$res = $jstree->_get_node($id);
$datas = $jstree->get_datas($res['id']);
$host = $res['type'] == 'default' ? $res['title'] : 'aggregator_'.$res['id'];
// ps-1335804082.gz
$psdir = "$notification_path/$host/top";
$time = isset($_GET['time']) ? $_GET['time'] : time();
$t1 = $t2 = false;
$i = 0;
while ($time-- && $i < 240 && !$t2) {
	$i++;
	if (file_exists("$psdir/ps-$time.gz")) {
		if ($t1) { $t2 = $time; } else { $t1 = $time; }
	}
}

if (!$t1 || !$t2) { echo json_encode(array()); exit; }

$data1 = get_ps_hash(implode("\n", gzfile("$psdir/ps-$t2.gz")));
$data2 = get_ps_hash(implode("\n", gzfile("$psdir/ps-$t1.gz")));
$data = array_intersect_uassoc($data1, $data2, "strcmp");
calc_time_derive($data, $data2);

//$data = array_slice($data, 63);
echo json_encode(array('data' => array_values($data), 'date' => $t1));

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
		$data[$key]['pid'] = $key;
		$data[$key]['userlabel'] = $data[$key]['user'];
		$data[$key]['grouplabel'] = $data[$key]['group'];
        $data[$key]['stime'] = $data2[$key]['stime'] - $data[$key]['stime'];
        $data[$key]['utime'] = $data2[$key]['utime'] - $data[$key]['utime'];
        $data[$key]['cpu'] = ceil(($data[$key]['utime'] + $data[$key]['stime']) / 100);
        $data[$key]['rss'] *= 4096;
    }
}
?>
