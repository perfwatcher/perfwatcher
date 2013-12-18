<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
$post_request = file_get_contents('php://input');
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);
if(isset($collectd_source) && $collectd_source) {
    if(isset($collectd_sources[$collectd_source])) {
        $url_jsonrpc = $collectd_sources[$collectd_source]['jsonrpc'];
        $proxy_jsonrpc = isset($collectd_sources[$collectd_source]['proxy'])?$collectd_sources[$collectd_source]['proxy']:null;
    } else {
        pw_error_log("Some node in your tree as Collectd Source set as '$collectd_source' but there is no such Source in your configuration. "
                ."Using default '$collectd_source_default' source instead. "
                ."Check your database (try [SELECT * FROM tree WHERE datas LIKE '%$collectd_source%';]) and your configuration file",  __FILE__, __LINE__);
        $url_jsonrpc = $collectd_sources[$collectd_source_default]['jsonrpc'];
        $proxy_jsonrpc = isset($collectd_sources[$collectd_source_default]['proxy'])?$collectd_sources[$collectd_source_default]['proxy']:null;
    }
} else {
    pw_error_log("This was called with no/empty \$collectd_source. More information is following.",  __FILE__, __LINE__);
    pw_error_log("\$collectd_source='".(isset($collectd_source)?$collectd_source:"unset")."'",  __FILE__, __LINE__);
    pw_error_log("\$post_request='$post_request'",  __FILE__, __LINE__);
    pw_error_log("\$_GET='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
    pw_error_log("\$_POST='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
    pw_error_log("Please tell us about this problem.",  __FILE__, __LINE__);
    $url_jsonrpc = $collectd_sources[$collectd_source_default]['jsonrpc'];
    $proxy_jsonrpc = isset($collectd_sources[$collectd_source_default]['proxy'])?$collectd_sources[$collectd_source_default]['proxy']:null;
}
putenv('http_proxy');
putenv('https_proxy');
$ch = curl_init($url_jsonrpc);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_jsonrpc == null ? FALSE : TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post_request))
        );
if($result = curl_exec($ch)) {
	echo $result;
} else {
    exit_error(curl_error($ch));
}

function exit_error($error) {
    echo json_encode(array("error" => $error, "data" => array())); exit;
}
