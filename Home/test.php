
<?php
$api_key = "AIzaSyBUmLMLSBIuTDqdsCMJrAG6RxhBWWiS-8M"; 
$url = "https://generativelanguage.googleapis.com/v1/models?key=$api_key";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if (isset($result['error'])) {
    echo "<h1>Lỗi API:</h1>";
    echo "<pre>";
    print_r($result['error']['message']);
    echo "</pre>";

} elseif (isset($result['models'])) {
    echo "<h1>Danh sách Models có sẵn cho API Key của bạn:</h1>";
    echo "<pre>";
    print_r($result['models']); 
    echo "</pre>";

} else {
    echo "<h1>Không nhận được phản hồi hợp lệ.</h1>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
}
?>