<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();
$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;
$netname = array_key_exists('netname', $_GET) ? $_GET['netname'] : false;

if ($netname) {
    require('header.php');
    echo "<div class='container mt-4'>";
    if($_POST){
        $url = "http://" . $api_location . "/api/vm/networks.php?action=conf&network_name=" . $_GET['netname'];
        $data = array('xml' => $_POST['xml']);

        $editCode = httpPut($url, $data);
        $editCode = json_decode($editCode, true);
        if ($editCode["status"] === 1) {
            function_alert('alert-success', "Success", $editCode['status_message']);
        } else if ($editCode["status"] === 0) {
            function_alert('alert-danger', "Error", $editCode['status_message']);
        }
    }
    if ($action) {
        $url = "http://" . $api_location . "/api/vm/networks.php?network_name=" . $netname;
        $data = array('action' => $action);

        $startCode = httpPut($url, $data);
        $startCode = json_decode($startCode, true);
        if ($startCode["status"] === 1) {
            function_alert('alert-success', "Success", $startCode['status_message']);
        } else if ($startCode["status"] === 0) {
            function_alert('alert-danger', "Error", $startCode['status_message']);
        }
    }

    $response = httpGet('http://' . $api_location . '/api/vm/networks.php?network_name=' . $netname);
    $response = json_decode($response, true);
    if (isset($response['status'])) {
        if ($response['status'] === 2) {
            function_alert('alert-warning', "Warning", $response['status_message']);
        } else if ($response['status'] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        }
    } else {
        $xml = httpGet('http://' . $api_location . '/api/vm/networks.php?action=conf&network_name=' . $netname);
        $errorcode = json_decode($xml, true);
        if ($errorcode !== null) {
            if (isset($errorcode['status'])) {
                if ($errorcode['status'] === 2) {
                    function_alert('alert-warning', "Warning", $errorcode['status_message']);
                } else if ($errorcode['status'] === 0) {
                    function_alert('alert-danger', "Error", $errorcode['status_message']);
                }
            }
        } else {
            echo '<div style="display: -webkit-box" class="container mt-5">';
            echo "<h1>Network: " . $response['network_name'] . "</h1>";
            if ($response['activity'] === "Active") {
                echo '<a type="button" href="?action=network-stop&amp;netname=' . $response['network_name'] . '" style="color: white" class="btn btn-danger ml-2 mt-2">Stop network</a>';
            } else {
                echo '<a type="button" href="?action=network-start&amp;netname=' . $response['network_name'] . '" style="color: white" class="btn btn-success ml-2 mt-2">Start network</a>';
            }
            echo '</div>';
            echo '<div class="container mt-4">
            <table class="table table-responsive">
              <tbody class="table-bordered table-dark">
                <tr>
                    <th scope="row"><b>Gateway IP Address:</b></th>
                    <td>' . $response['gateway_ip'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>IP Address Range:</b></th>
                    <td>' . $response['ip_range'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Forwarding:</b></th>
                    <td>' . $response['forward'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>DHCP Range:</b></th>
                    <td>' . $response['dhcp'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>Network state:</b></th>
                    <td>' . $response['activity'] . '</td>
                </tr>
                <tr>
                    <th scope="row"><b>XML Definition:</b></th>
                    <td>
                        <form method="post">
                        <div class="form-group">
                            <label for="xmlDef">XML:</label>
                            <textarea class="form-control" name="xml" rows="20" cols="100" id="xmlDef">' . $xml . '</textarea>
                            <button type="submit" class="btn btn-light mt-2">Edit</button>
                        </div>
                        </form>
                    </td>
                </tr>
              </tbody>
            </table>
        </div>';
        }
    }
}
