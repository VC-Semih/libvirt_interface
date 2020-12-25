<?php
require_once('tools.php');
global $api_location;
if (!empty($_POST)) {
    if (isset($_POST['group_name'])) {
        $sucessMsg = "";
        $errorMsg = "";
        $foundError = false;
        $foundSuccess = false;
        foreach ($_POST['group_name'] as $group_name) {
            $data["group_name"] = $group_name;
            $response = httpPost('http://' . $api_location . '/api/vm/group.php', $data);
            $response = json_decode($response, true);
            if($response['status'] == '1'){
                $sucessMsg .= $group_name.': '.$response['status_message'].'<br>';
                $foundSuccess = true;
            }
            else if($response['status'] == '0'){
                $errorMsg .= $group_name.': '.$response['status_message'].'<br>';
                $foundError = true;
            }
        }
        if($foundSuccess){
            echo '<div class="alert alert-success" id="errorData" role="alert">'.$sucessMsg.'</div>';
        }
        if($foundError){
            echo '<div class="alert alert-danger" id="errorData" role="alert">'.$errorMsg.'</div>';
        }
    }
}
?>
