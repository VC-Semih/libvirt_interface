<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

require('header.php');
echo "<div class='container mt-4'>";
if (!empty($_POST)) {
    $response = httpPost('http://'. $api_location . '/api/vm/vm.php',$_POST);
    $response = json_decode($response,true);
    if(isset($response['status'])){
        if($response['status'] === 0) {
            function_alert('alert-danger', 'Error', $response['status_message']);
            die();
        }else if ($response['status'] === 1){
            function_alert('alert-success', "Success", $response['status_message'].'<br/>You will be redirected in 3 seconds');
            header("refresh:3;url=index.php" );
            die();
        }
    }
}
$response = httpGet('http://' . $api_location . '/api/vm/volume.php?pool=images');
$volumes = json_decode($response, true);
if (isset($volumes['status'])) {
    if ($volumes['status'] === 0) {
        function_alert('alert-danger', 'Error', $volumes['status_message']);
        die();
    } else if ($volumes['status'] === 2) {
        function_alert('alert-warning', 'Warning', $volumes['status_message']);
        $volumes = array();
    }
}

echo "<h1>Create new domain</h1>";
echo '<form method="POST" class="mt-4">
        <div class="form-row align-items-center">
            <div class="col-md-4 mt-1">
                <label>Name of the virtual machine:</label>
            </div>
            <div class="col-md-5 mt-1">
                <input type="text" required="required" class="form-control" name="vmname"/>
            </div>
            <div class="col-md-4 mt-1">
                <label>Choose RAM (Value Selected is in GB):</label>
            </div>
            <div class="col-md-5 mt-1">
                <select name="ram" class="form-control">
                    <option selected value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            <div class="c5 col-md-4 mt-1">
                <label>Choose Disk:</label>
            </div>
            <div class="col-md-5 mt-1">
                <select name="disk" class="form-control">
                ';
foreach ($volumes as $volume) {
    echo '<option value = "' . $volume['volume_path'] . '" >' . $volume['volume_name'] . '</option >';
}
echo '</select>
            </div>
            <div class="col-md-2 mt-1">
                <a href="storage.php" type="button" class="btn btn-dark">Manage disks</a>
            </div>
            <div class="col-md-4 mt-1">
                <label>CPU CORES:</label></div>
            <div class="col-md-5 mt-1">
                <select name="cpucores" class="form-control">
                    <option selected value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>
            <div class="col-md-12"></div>
            <div class="col-md-2" id="submit-button">
                <input type="submit" class="btn btn-dark" value="Create new domain"/>
            </div>
        </div>
       </form>';