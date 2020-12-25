<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;


require('header.php');

echo "<div class='container mt-4'>";
if ($action) {
    $url = "http://" . $api_location . "/api/vm/networks.php?network_name=" . $_GET['netname'];
    $data = array('action' => $action);

    $response = httpPut($url, $data);
    $response = json_decode($response, true);
    if ($response["status"] === 1) {
        function_alert('alert-success', "Success", $response['status_message']);
    } else if ($response["status"] === 0) {
        function_alert('alert-danger', "Error", $response['status_message']);
    }
}
$response = httpGet('http://' . $api_location . '/api/vm/networks.php');
$response = json_decode($response, true);
if (isset($response['status'])) {
    if ($response['status'] === 2) {
        function_alert('alert-warning', "Warning", $response['status_message']);
    }
} else {
    echo "<h1>Networks</h1>" .
        "<table class='table mt-5 table-responsive'>" .
        "<thead class='table-dark'>" .
        "<tr>" .
        "<th>Network name</th>" .
        "<th>Network state</td>" .
        "<th>Gateway IP Address</th>" .
        "<th>IP Address Range</th>" .
        "<th>Forwarding</th>" .
        "<th>DHCP Range</th>" .
        "<th>Actions</th>" .
        "</tr>" .
        "</thead>" .
        "<tbody class='table-dark'>";

    foreach ($response as $net) {
        echo
            "<tr>" .
            "<td>" . $net['network_name'] . "</td>" .
            "<td>" . $net['activity'] . "</td>" .
            "<td>" . $net['gateway_ip'] . "</td>" .
            "<td>" . $net['ip_range'] . "</td>" .
            "<td>" . $net['forward'] . "</td>" .
            "<td>" . $net['dhcp'] . "</td>";
        echo "<td style='align-content: center'>";
        if ($net['activity'] === "Active") {
            echo "<a type='button' style='color: black' class='btn btn-light m-1' href=\"?action=network-stop&amp;netname=" . $net['network_name'] . "\">Stop network </a>";
        } else {
            echo "<a type='button' style='color: black' class='btn btn-light m-1' href=\"?action=network-start&amp;netname=" . $net['network_name'] . "\">Start network </a>";
        }
        echo "<a type='button' style='color: black' class='btn btn-light m-1' href=\"network_configuration.php?netname=" . $net['network_name'] . "\">Modify configuration</a>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody>" .
        "</table>";
}

echo "</div>" .
    "</body>" .
    "</html>";
?>
