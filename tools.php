<?php
$api_location = '10.3.134.117/libvirt_api/';
function function_alert($msgType, $msgTitle, $msg)
{
    echo '<div class="alert ' . $msgType . ' alert-dismissible fade show mt-5" role="alert">
        <h4 class="alert-heading">' . $msgTitle . '</h4>
        <p>' . $msg . '</p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        </div>';
}

function httpGet($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (empty($return_code)) {
        function_alert('alert-danger', 'Error', 'API is down');
        die();
    }
    curl_close($ch);
    return $output;
}

function httpPut($url, $params)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $output = curl_exec($ch);
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (empty($return_code)) {
        function_alert('alert-danger', 'Error', 'API is down');
        die();
    }
    curl_close($ch);
    return $output;
}

function httpPost($url, $params)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $output = curl_exec($ch);
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (empty($return_code)) {
        function_alert('alert-danger', 'Error', 'API is down');
        die();
    }
    curl_close($ch);
    return $output;
}

function httpDelete($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (empty($return_code)) {
        function_alert('alert-danger', 'Error', 'API is down');
        die();
    }
    curl_close($ch);
    return $output;
}