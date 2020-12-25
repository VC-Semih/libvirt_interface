<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

$uuid = array_key_exists('uuid', $_GET) ? $_GET['uuid'] : false;

require('header.php');
echo "<div class='container mt-4'>";
if ($uuid && strlen(trim($uuid)) > 0) {
    $url = "http://" . $api_location . "/api/vm/vm.php?uuid=" . $uuid;
    $response = httpGet($url);
    $vm = json_decode($response, true);
    if (isset($vm['status'])) {
        if ($vm['status'] === 0) {
            function_alert('alert-danger', 'Error', $vm['status_message']);
            die();
        }
    }

    $url = "http://" . $api_location . "/api/vm/vmnetwork.php?uuid=" . $uuid;
    $response = httpGet($url);
    $vm_networks = json_decode($response, true);
    if (isset($vm_networks['status'])) {
        if ($vm_networks['status'] === 0) {
            function_alert('alert-danger', 'Error', $vm_networks['status_message']);
            die();
        }
    }

} else {
    function_alert('alert-danger', 'Error', 'Please specify uuid!');
    die();
}
echo '
    <h1>Domain information - ' . $vm['domain_name'] . '</h1>
        <div class="container mt-4">
            <table class="table table-responsive">
              <tbody class="table-bordered table-dark">
                <tr>
                    <th scope="row"><b>Domain name:</b></th>
                    <td>' . $vm['domain_name'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain UUID:</b></th>
                    <td>' . $vm['domain_uuid'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain type:</b></th>
                    <td>' . $vm['domain_type'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain emulator:</b></th>
                    <td>' . $vm['domain_emulator'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain memory:</b></th>
                    <td>' . $vm['domain_memory'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Number of CPUs:</b></th>
                    <td>' . $vm['domain_cpu'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain state:</b></th>
                    <td>' . $vm['domain_state'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain architecture:</b></th>
                    <td>' . $vm['domain_arch'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Domain ID:</b></th>
                    <td>' . $vm['domain_id'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>VNC Port:</b></th>
                    <td>' . $vm['VNC Port'] . '</td>
                </tr>
              </tbody>
            </table>
        </div>';


echo "<h3 class='mt-4'>Network devices</h3>";
if (isset($vm_networks['status'])) {
    if ($vm_networks['status'] === 2) {
        function_alert('alert-warning', 'Warning', $vm_networks['status_message']);
        die();
    }
} else {
    echo "<div class='container'>" .
        "<table class='table mt-5 table-responsive'>" .
        "<thead class='table-dark'>" .
        "<tr>" .
        "<th>MAC Address</th>" .
        "<th>NIC Type</td>" .
        "<th>Network</th>" .
        "<th>Network active</th>" .
        "</tr>" .
        "</thead>" .
        "<tbody class='table-dark'>";
    foreach ($vm_networks as $vm_network) {
        echo
            "<tr>" .
            "<td>" . $vm_network['mac_addr'] . "</td>" .
            "<td>" . $vm_network['nic_type'] . "</td>" .
            "<td>" . $vm_network['network'] . "</td>" .
            "<td>" . $vm_network['is_network_active'] . "</td>" .
            "</tr>";

    }
    echo "</tbody>" .
        "</table>" .
        "</div>";
}
echo "</div>" .
    "</body>" .
    "</html>";
