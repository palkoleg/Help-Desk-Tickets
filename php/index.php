<?php
require __DIR__ . '/vendor/autoload.php';

include_once 'APIService.php';
include_once 'CSVService.php';

echo '<form action="index.php" method="post">
        Use a token as a password?:<input type="hidden" name="token" value="0" />
                                   <input type="checkbox" name="token" value="1" /> <br><br>
        Enter your subdomain:      <input type="text" name="subdomain" /><br><br>
        Enter your username:       <input type="text" name="username" /><br><br>
        Enter your password:       <input type="text" name="password" /><br><br>
        <input type="submit" name="submit" value="Enter data" />
      </form>';

if (isset($_POST["token"]) && isset($_POST["subdomain"]) && isset($_POST["username"]) && isset($_POST["password"])) {
    $token = false;
    if ($_POST["token"]=="1") {
        $token = true;
    }
    $subdomain = $_POST["subdomain"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    $client = new GuzzleHttp\Client();

    $apiService = new APIService($token, $subdomain, $username, $password);
    $data = $apiService->SaveResponse($apiService->firstResponse());
    $tickets = $data[0];
    $hasMore = $data[1];
    $next = $data[2];
    $csvHandler = new CSVService();
    $csvHandler->headersToCSV();
    $csvHandler->saveArrayToCSV($tickets);

    while($hasMore) {
        $data = $apiService->SaveResponse($apiService->nextResponse($next));
        $tickets = $data[0];
        $hasMore = $data[1];
        $next = $data[2];
        $csvHandler->saveArrayToCSV($tickets);
    }

    echo '<b>Successfully saved to CSV file</b>';
} else {
    if (isset($_POST["submit"])) {
        echo 'Please enter all data';
    }
}