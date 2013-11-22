<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
$post_request = file_get_contents('php://input');
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);
if(isset($collectd_source) && $collectd_source && isset($collectd_sources[$collectd_source])) {
    $url_jsonrpc = $collectd_sources[$collectd_source]['jsonrpc'];
} else {
    pw_error_log("This line should not be executed. Please tell us...",  __FILE__, __LINE__);
    $url_jsonrpc = $jsonrpc_server;
}
putenv('http_proxy');
putenv('https_proxy');
$ch = curl_init($url_jsonrpc);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $jsonrpc_topps_httpproxy == null ? FALSE : TRUE);
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
