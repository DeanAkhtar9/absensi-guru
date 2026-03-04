<?php

require 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('config/google-service-account.json');
$client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

$service = new Google_Service_Sheets($client);

$spreadsheetId = '1yeYr0ETjoEHmx5HYrS5j2pIu9MJSjotgkffn31JAd4I';
$range = 'Sheet1!A2:E';

try {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $rows = $response->getValues();

    if (empty($rows)) {
        echo "Tidak ada data.";
    } else {
        foreach ($rows as $row) {
            echo $row[0] . " - " . $row[2] . " - " . $row[4] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}