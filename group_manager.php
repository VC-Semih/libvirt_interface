<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;
$group_name = array_key_exists('group_name', $_GET) ? $_GET['group_name'] : false;

if ($group_name) {
    require('header.php');
    echo "<div class='container mt-4'>";

    if ($action) {
        $response = httpGet('http://' . $api_location . '/api/vm/group_member.php?group_name=' . $group_name);
        $response = json_decode($response, true);
        if (isset($response['status'])) {
            if ($response['status'] === 2) {
                function_alert('alert-warning', "Warning", $response['status_message']);
            } else if ($response['status'] === 0) {
                function_alert('alert-danger', "Error", $response['status_message']);
            }
        } else {
            $massActions = array('domain-start', 'domain-suspend', 'domain-stop', 'domain-resume', 'domain-delete');
            if (in_array($action, $massActions)) {
                $success = "";
                $errors = "";
                foreach ($response[$group_name][0] as $group_member) {
                    if ($action === 'domain-start' || $action === 'domain-suspend' || $action === 'domain-stop' || $action === 'domain-resume') {
                        $url = "http://" . $api_location . "/api/vm/vmstate.php?uuid=" . $group_member;
                        $data = array('action' => $action);
                        $response2 = httpPut($url, $data);
                    } else if ($action === 'domain-delete') {
                        $url = "http://" . $api_location . "/api/vm/vm.php?uuid=" . $group_member;
                        $response2 = httpDelete($url);
                    }
                    if (isset($response2)) {
                        $response2 = json_decode($response2, true);
                        if ($response2["status"] === 1) {
                            $success .= 'UUID: ' . $group_member . ' ► ' . $response2['status_message'] . '<br><br>';
                        } else if ($response2["status"] === 0) {
                            $errors .= 'UUID: ' . $group_member . ' ► ' . $response2['status_message'] . '<br><br>';

                        }
                    }
                }
                if (strlen($success) > 0) {
                    function_alert('alert-success', "Success", $success);
                }
                if (strlen($errors) > 0) {
                    function_alert('alert-danger', "Error", $errors);
                }
            } else {
                if ($action === 'group-delete') {
                    $url = "http://" . $api_location . "/api/vm/group.php?group_name=" . $_GET['group_name'];

                    $response = httpDelete($url);
                    $response = json_decode($response, true);
                    if ($response["status"] === 1) {
                        function_alert('alert-success', "Success", $response['status_message']);
                        echo "<meta http-equiv=\"refresh\" content=\"3;url=groups.php\"/>";
                    } else if ($response["status"] === 0) {
                        function_alert('alert-danger', "Error", $response['status_message']);
                    } else if ($response['status'] === 2) {
                        function_alert('alert-warning', "Warning", $response['status_message']);
                    }
                } else if ($action === 'member-delete') {
                    if (isset($_GET['uuid'])) {
                        $url = "http://" . $api_location . "/api/vm/group_member.php?group_name=" . $group_name . "&uuid=" . $_GET['uuid'];
                        $response = httpDelete($url);
                        $response = json_decode($response, true);
                        if ($response["status"] === 1) {
                            function_alert('alert-success', "Success", $response['status_message']);
                        } else if ($response["status"] === 0) {
                            function_alert('alert-danger', "Error", $response['status_message']);
                        } else if ($response['status'] === 2) {
                            function_alert('alert-warning', "Warning", $response['status_message']);
                        }
                    } else {
                        function_alert('alert-warning', "Warning", 'Missing arguments !');
                    }
                }
            }
        }
    }

    $response = httpGet('http://' . $api_location . '/api/vm/group_member.php?group_name=' . $group_name);
    $response = json_decode($response, true);
    if (isset($response['status'])) {
        if ($response['status'] === 2) {
            function_alert('alert-warning', "Warning", $response['status_message']);
        } else if ($response['status'] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        }
    } else {
        echo '<div id="alert-'.$group_name.'"></div>';
        echo '<div style="display: -webkit-box" class="container mt-5">';
        echo '<h2>Group: <span contenteditable data-name="custom-text" id="'.$group_name.'">' . $group_name . '</span></h2>';
        echo '<a type="button" href="?action=group-delete&amp;group_name=' . $group_name . '" style="color: white" class="btn btn-danger ml-2">Remove group</a>';
        echo '</div>';
        echo "<table class='table ml-5 table-responsive'>" .
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
        foreach ($response[$group_name][0] as $group_member) {
            $url = "http://" . $api_location . "/api/vm/vm.php?uuid=" . $group_member;
            $responseVm = httpGet($url);
            $vm = json_decode($responseVm, true);
            if (isset($vm['status'])) {
                if ($vm['status'] === 0) {
                    $vm['domain_name'] = 'Non-existent domain';
                    $vm['domain_uuid'] = $group_member;
                    $vm['domain_id'] = '-1';
                    $vm['domain_state'] = '';
                }
            }
            echo
                "<tr>" .
                "<td> <a href=domain_info.php?uuid=" . $vm['domain_uuid'] . ">" . $vm['domain_name'] . "</a></td>" .
                "<td>" . $vm['domain_uuid'] . "</td>" .
                "<td>" . $vm['domain_id'] . "</td>" .
                "<td>" . $vm['domain_state'] . "</td> " .
                "<td><a type='button' href='?action=member-delete&amp;group_name=" . $group_name . "&amp;uuid=" . $vm['domain_uuid'] . "' style='color: white' class='btn btn-danger'>Remove from group</a></td>";
            echo "</tr>";
        }

        echo "</tbody>" .
            "</table>";
        echo "<div class='container'>
                <h2>Domains state:</h2>
                <div class='row justify-content-md-left ml-2'>
                    <div class='col-md-auto'>
                        <a type='button' href='?group_name=" . $group_name . "&amp;action=domain-start' style='color: white' class='btn btn-dark'>Start domains</a>
                    </div>
                    <div class='col-md-auto'>
                        <a type='button' href='?group_name=" . $group_name . "&amp;action=domain-suspend' style='color: white' class='btn btn-dark'>Suspend domains</a>
                    </div>
                    <div class='col-md-auto'>
                        <a type='button' href='?group_name=" . $group_name . "&amp;action=domain-stop' style='color: white' class='btn btn-dark'>Stop domains</a>
                    </div>
                    <div class='col-md-auto'>
                        <a type='button' href='?group_name=" . $group_name . "&amp;action=domain-resume' style='color: white' class='btn btn-dark'>Resume domains</a>
                    </div>
                    <div class='col-md-auto'>
                        <a type='button' href='?group_name=" . $group_name . "&amp;action=domain-delete' style='color: white' class='btn btn-danger'>Destroy domains</a>
                    </div>
                </div>
              </div>";
    }
}
?>
<script type="text/javascript">
    const api_location = '<?php echo($api_location) ?>';
    document.addEventListener('keydown', function (event) {
        let esc = event.which === 27,
            nl = event.which === 13,
            el = event.target,
            input = el.nodeName !== 'INPUT' && el.nodeName !== 'TEXTAREA';

        if (input) {
            if (esc) {
                // restore state
                document.execCommand('undo');
                el.blur();
            } else if (nl) {
                // save
                event.preventDefault();
                let id = "alert-"+el.id;
                let element = document.getElementById(id);
                $.ajax({
                    url: "http://" + api_location + "/api/vm/group.php?old_name="+el.id,
                    data: {new_name: el.innerText},
                    type: 'PUT',
                    success : function(data){
                        element.innerHTML = "<div class=\"alert alert-success\" role=\"alert\">\n" +
                            data['status_message']  +
                            "</div>"
                        el.id=el.innerText;
                        element.id="alert-"+el.id;

                        setTimeout(function () {
                            window.location = window.location.href.split("?")[0] + "?group_name="+el.id
                        }, 3000);

                    },
                    error : function(resultat){
                        element.innerHTML = "<div class=\"alert alert-danger\" role=\"alert\">\n" +
                            resultat['responseJSON']['status_message']  +
                            "</div>"
                    },
                })
                el.blur();
            }
        }
    }, true);
</script>

