<?php
require('ContainerTrackingApi.php');

$apiWrapper = new ContainerTrackingApi('YOUR_API_KEY');

try {
    $result = $apiWrapper->container('MEDU6965343', 'MSCU');
    print_r(json_decode($result));
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}