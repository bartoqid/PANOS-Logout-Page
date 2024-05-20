<!DOCTYPE html>
<html>

<head>
<style>
@import "bourbon";

body {
    height: 100%;
    width: 100%;
    margin: 0;
    padding: 0;
    background-color: black;
}

.container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.btn {
    color: black;
    font-weight: bold;
    font-size: 30px;
    border: 1px solid white;
    padding: 20px 80px;
    text-transform: uppercase;
    letter-spacing: 4px;
    position: relative;
    overflow: hidden;
    cursor: pointer;

    span {
        font-family: 'Roboto', sans-serif;
        position: relative;
        z-index: 100;
    }

    &:before, &:after {
        content:'';
        position: absolute;
        display: block;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    &:before {
        transform: translate3d(-100%, 0, 0);
        background-color: white;
        border: 1px solid white;

        transition: transform 300ms $ease-in-cubic;
    }

    &:after {
        background-color: #ffd1d8;
        border: 1px solid #ffd1d8;
        transform: translate3d(100%, 0, 0);

        transition: transform 300ms 300ms cubic-bezier(.16,.73,.58,.62);
    }

    &:hover {
        &:before {
            transform: translate3d(0,0,0);
        }

        &:after {
            transform: translate3d(0,0,0);
        }
    }
}
</style>
</head>


<body>


<div class="container">
<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
  <button type="submit" name="someAction" class="btn"><span>Logout</span></button>
</form>
</div>


</body>

<?php

//Get the client ip from the browser
if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['someAction']))
    {
    function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
        }

    //Get firewall ip and api key from config.ini and load into an array
    $ini_array = parse_ini_file("config.ini");
    $firewall = $ini_array['firewall'];
    $key = $ini_array['key'];

    //Get client IP
    $internal_ip = get_client_ip();

    //Send the first API to clear data plane mapping
    $ch = curl_init();
    $url = "https:"."/"."/" .$firewall. "/api/?type=op&key=" .$key. "&vsys=vsys1&cmd=<clear><user-cache-mp><ip>".$internal_ip. "</ip></user-cache-mp></clear>";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //close the channel
    $output = curl_exec($ch) or die( curl_error($ch) );
    curl_close($ch);

    //send the second API to clear the management plane mapping
    $ch1 = curl_init();
    $url = "https:"."/"."/" .$firewall. "/api/?type=op&key=" .$key. "&vsys=vsys1&cmd=<clear><user-cache><ip>".$internal_ip. "</ip></user-cache></clear>";
    curl_setopt($ch1, CURLOPT_URL, $urlmp);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);

    //close the channel
    $output1 = curl_exec($ch1)  or die( curl_error($ch1) );
    curl_close($ch1);

    unset($ch);
    unset($ch1);

    }
?>

</html>
