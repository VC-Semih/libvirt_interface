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
echo "<h1>Virtual machines: </h1>";
if ($action) {
    if ($action !== 'domain-delete') { //If it's not equal to domain delete we just change the state so PUT request
        $url = "http://" . $api_location . "/api/vm/vmstate.php?uuid=" . $_GET['uuid'];
        $data = array('action' => $action);

        $response = httpPut($url, $data);
        $response = json_decode($response, true);
        if ($response["status"] === 1) {
            function_alert('alert-success', "Success", $response['status_message']);
        } else if ($response["status"] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        }
    } else if ($action === 'domain-delete') { //If it's domain delete we need to do a DELETE request
        $url = "http://" . $api_location . "/api/vm/vm.php?uuid=" . $_GET['uuid'];
        $response = httpDelete($url);
        $response = json_decode($response, true);
        if ($response["status"] === 1) {
            function_alert('alert-success', "Success", $response['status_message']);
        } else if ($response["status"] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        }
    }
}
$response = httpGet('http://' . $api_location . '/api/vm/vm.php');
$response = json_decode($response, true);
if (isset($response['status'])) {
    if ($response['status'] === 2) {
        function_alert('alert-warning', "Warning", $response['status_message']);
    }
} else {
    echo "<table class='table mt-5 table-responsive'>" .
        "<thead class='table-dark'>" .
        "<tr>" .
        "<th>Name</th>" .
        "<th>UUID</td>" .
        "<th>ID</th>" .
        "<th>State</th>" .
        "<th>Actions</th>" .
        "</tr>" .
        "</thead>" .
        "<tbody class='table-dark'>";

    foreach ($response as $vm) {
        echo
            "<tr>" .
            "<td> <a href=domain_info.php?uuid=" . $vm['domain_uuid'] . ">" . $vm['domain_name'] . "</a></td>" .
            "<td>" . $vm['domain_uuid'] . "</td>" .
            "<td>" . $vm['domain_id'] . "</td>" .
            "<td>" . $vm['domain_state'] . "</td>";
        echo "<td style='align-content: center'>";
        if ($vm['domain_state'] === "running") {
            echo "<a type='button' style='color: black' class='btn btn-light m-1' href=\"?action=domain-stop&amp;uuid=" . $vm['domain_uuid'] . "\">Stop domain</a>";
        } else if ($vm['domain_state'] === "shutdown" || $vm['domain_state'] === "shutoff") {
            echo "<a type='button' style='color: black' class='btn btn-light m-1' href=\"?action=domain-start&amp;uuid=" . $vm['domain_uuid'] . "\">Start domain</a>";
        }
        if ($vm['domain_state'] === "paused") {
            echo "<a type='button' class='btn btn-warning m-1' href=\"?action=domain-resume&amp;uuid=" . $vm['domain_uuid'] . "\">Resume domain</a>";
        } else {
            echo "<a type='button' class='btn btn-warning m-1' href=\"?action=domain-suspend&amp;uuid=" . $vm['domain_uuid'] . "\">Suspend domain</a>";
        }
        echo " <a type='button' class='btn btn-danger m-1' href=\"?action=domain-delete&amp;uuid=" . $vm['domain_uuid'] . "\">Delete domain</a>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody>" .
        "</table>";
}
echo '<a  href="createdomain.php" type="button" class="btn btn-dark">Create new virtual machine</a>';

echo "</div>" .
    "</body>" .
    "</html>";
?>
