<?php
require("ContainerTrackingApi.php");

$apiWrapper = new ContainerTrackingApi('YOUR_API_KEY');

try {
    $result = $apiWrapper->container('MEDU6965343');
    print_r(json_decode($result));
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
