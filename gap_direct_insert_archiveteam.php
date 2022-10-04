<?php
//Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$shard = null;

if (isset($argc)) {
    if (isset($argv[1])) {
        $shard = $argv[1];
    }
    for ($i = 0; $i < $argc; $i++) {
        echo "Argument #" . $i . " - " . $argv[$i] . "\n";
    }
} else {
    echo "argc and argv disabled\n";
}

$array_urls_to_fetch = get_urls_to_download();
foreach ($array_urls_to_fetch as $line) {
    $url = get_url_from_fid($line);

    $response = file_get_contents_curl($url);
    if ($response != "4xx" && $response != null) {
        $resp = send_data_to_dges($response, $line);
        echo $resp . "\n";
    } else {
        echo "4xx\n";
    }
}

function send_data_to_dges($data, $feed_id)
{
    $post_data = array(
        'data' => $data,
        'feed_id' => $feed_id
    );

    $post_data_base64 = base64_encode(serialize($post_data));

    $url = base64_decode("aHR0cHM6Ly9kYXRhLmdlc2NobWVpZGlnLmVzL3Byb2plY3RzL3Byb2plY3RfZmVlZGx5X3NjcmFwZXIvaW5zZXJ0X2RhdGFfZ2l0aHViX2FjdGlvbl9hcmNoaXZldGVhbS5waHA=");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_base64);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
    return $server_output;
}

function get_url_from_fid($fid)
{
    $url = base64_decode('aHR0cHM6Ly9mZWVkbHkuY29tL3YzL2ZlZWRzLw==') . urlencode($fid) . base64_decode('P251bVJlY2VudEVudHJpZXM9MyZjaz0xNjYzNDUzNDA4MjQ0JmN0PWZlZWRseS5kZXNrdG9wJmN2PTMxLjAuMTY2OA==');
    echo $url . "\n";
    return $url;
}

function file_get_contents_curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $data = curl_exec($ch);
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo $error_msg;
    }

    curl_close($ch);
    if ($statuscode == "403" || $statuscode == "429" || $data == "Internal Server Error") {
        return "4xx";
    }
    return $data;
}

function get_urls_to_download()
{
    global $shard;
    if ($shard == null) {
        $shard = 0;
    }
    $url = base64_decode("aHR0cHM6Ly9kYXRhLmdlc2NobWVpZGlnLmVzL3Byb2plY3RzL3Byb2plY3RfZmVlZGx5X3NjcmFwZXIvdXJsc19mb3JfcHJveHlfYXJjaGl2ZXRlYW0ucGhwP3NoYXJkPQ==");
    $url = $url . $shard;
    $stringified_data = file_get_contents($url);

    $array_data = unserialize($stringified_data);
    return $array_data;
}
