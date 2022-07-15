<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShowAllHeaders</title>
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
</head>
<body>
    
<h1>get_headers()</h1>
<?php
$URL = 'http://zoopla.robinpecha.cz/';

$headers = get_headers($URL);
foreach($headers as $value) {
    echo $value;
    echo "<br>";
}
?>

<h1>$_SERVER</h1>
<?php
function get_HTTP_request_headers() {
    $HTTP_headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $single_header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $HTTP_headers[$single_header] = $value;
    }
    return $HTTP_headers;
}
$headers = get_HTTP_request_headers();
foreach ($headers as $key => $value) {
    echo "$key => $value <br/>";
}
?>

<h1>apache_request_headers</h1>
<?php
$apache_headers= apache_request_headers();

foreach ($apache_headers as $key => $value) {
    echo "$key => $value <br/>";
}
?>

<h1>    getallheaders</h1>
<?php 
    $headers =  getallheaders();
    foreach($headers as $key=>$val){
    echo $key . ' => ' . $val . ' <br> ';}
?>

</body>
</html>
