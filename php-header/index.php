<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShowAllHeaders</title>
    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
    <style>
        .h1,
        h1 {
            background-color: lightgray;
            margin-top: 30px;
        }
    </style>
</head>

<body>

    <h1>MULTIZONAL CLUSTER</h1>
    <h2><?php echo gethostname(); ?> = hostname of this server </h2>
    <h2> <?php echo $_SERVER['REMOTE_ADDR'];   ?> = local IP of this server </h2>
    <h2><?php echo file_get_contents('https://ifconfig.me/ip'); ?> = public IP of this server</h2>
    <h2><span id="ip">?</span> = your public IP</h2>
    <script>
        fetch('https://ifconfig.me/ip')
            .then(response => response.text())
            .then(data => {
                console.log(data);
                document.getElementById("ip").innerHTML = (data);
            });
    </script>


    <h1>get_headers():</h1>
    <?php
$URL = 'http://zoopla.robinpecha.cz/';

$headers = get_headers($URL);
foreach($headers as $value) {
    echo $value;
    echo "<br>";
}
?>

    <h1>$_SERVER:</h1>
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

    <h1>apache_request_headers:</h1>
    <?php
$apache_headers= apache_request_headers();

foreach ($apache_headers as $key => $value) {
    echo "$key => $value <br/>";
}
?>

    <h1> getallheaders:</h1>
    <?php 
    $headers =  getallheaders();
    foreach($headers as $key=>$val){
    echo $key . ' => ' . $val . ' <br> ';}
?>

    <!-- <h1>Access log:</h1>
    <pre> -->
    <?php include("/var/log/apache2/access.log");  ?>
    </pre>

</body>

</html>

<!-- <?php phpinfo(); ?> -->
