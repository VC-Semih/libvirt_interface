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
if (!empty($_POST)) {
    if (isset($_POST['group_name']) && isset($_POST['uuid'])) {
        $response = httpPost('http://' . $api_location . '/api/vm/group_member.php', $_POST);
        $response = json_decode($response, true);
        if (isset($response['status'])) {
            if ($response['status'] === 0) {
                function_alert('alert-danger', 'Error', $response['status_message']);
            } else if ($response['status'] === 1) {
                function_alert('alert-success', "Success", $response['status_message']);
            }
        }
    }
}
if ($action) {
    if ($action === 'group-delete') {
        $url = "http://" . $api_location . "/api/vm/group.php?group_name=" . $_GET['group_name'];

        $response = httpDelete($url);
        $response = json_decode($response, true);
        if ($response["status"] === 1) {
            function_alert('alert-success', "Success", $response['status_message']);
        } else if ($response["status"] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        } else if ($response['status'] === 2) {
            function_alert('alert-warning', "Warning", $response['status_message']);
        }
    }else if($action === 'member-delete'){
        if(isset($_GET['group_name']) && isset($_GET['uuid'])){
            $url = "http://".$api_location."/api/vm/group_member.php?group_name=".$_GET['group_name']."&uuid=".$_GET['uuid'];
            $response = httpDelete($url);
            $response = json_decode($response, true);
            if ($response["status"] === 1) {
                function_alert('alert-success', "Success", $response['status_message']);
            } else if ($response["status"] === 0) {
                function_alert('alert-danger', "Error", $response['status_message']);
            } else if ($response['status'] === 2) {
                function_alert('alert-warning', "Warning", $response['status_message']);
            }
        }else{
            function_alert('alert-warning', "Warning", 'Missing arguments !');
        }
    }
}
$response = httpGet('http://' . $api_location . '/api/vm/group_member.php');
$response = json_decode($response, true);
if (isset($response['status'])) {
    if ($response['status'] === 2) {
        function_alert('alert-warning', "Warning", $response['status_message']);
    }
} else {
    foreach ($response as $group_name => $groups) {
        echo '<div id="alert-'.$group_name.'"></div>';
        echo '<div style="display: -webkit-box" class="container mt-5">';
            echo '<h2>Group: <span contenteditable data-name="custom-text" id="'.$group_name.'">' . $group_name . '</span></h2>';
            echo '<a type="button" href="group_manager.php?group_name=' . $group_name . '" style="color: white" class="btn btn-dark ml-2">Manage group</a>';
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

        foreach ($groups[0] as $group_member) {
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
        $response = httpGet('http://' . $api_location . '/api/vm/vm.php');
        $response = json_decode($response, true);
        if (isset($response['status'])) {
            if ($response['status'] === 0) {
                function_alert('alert-danger', "Error", $response['status_message']);
            }
        } else {
            echo '
                <form method="post">
                    <input type="text" name="group_name" value="' . $group_name . '" hidden/>
                    <div class="form-row align-items-center ml-5">
                        <div class="col-auto">
                            <label for="uuid" class="col-sm-2 col-form-label col-form-label-sm">Domains:</label>
                        </div>
                        <div class="col-sm-3">
                            <select id="uuid" name="uuid" class="form-control">';
            foreach ($response as $vm) {
                echo '<option value = "' . $vm['domain_uuid'] . '" >' . $vm['domain_name'] . '</option >';
            }
            echo '</select>
                        </div>
                        <div class="col-auto">
                            <input type="submit" class="btn btn-dark" value="Add domain"/>
                        </div>
                    </div>
                </form>';
        }
    }
}
echo '<div class="container">';
echo '
    <h5>Add group</h5>
    <div class="form-group">
        <form name="group_name" id="group_name">
            <div class="container">
            <div id="addMsg"></div>
                <table class="table table-bordered" id="dynamic_field">
                    <tr>
                        <td><input type="text" required name="group_name[]" placeholder="Enter group name"
                                   class="form-control name_list"/></td>
                        <td>
                            <button type="button" name="add" id="add" class="btn btn-dark">Add More</button>
                        </td>
                    </tr>
                </table>
                <input type="button" name="submit" id="submit" class="btn btn-dark" value="Submit"/>
            </div>
        </form>
    </div>
    </div>';
echo "</div>" .
    "</body>" .
    "</html>";
?>
<script>
    const api_location = '<?php echo($api_location) ?>';
    $(document).ready(function () {
        var i = 1;
        $('#add').click(function () {
            i++;
            $('#dynamic_field').append('<tr id="row' + i + '"><td><input type="text" required name="group_name[]" placeholder="Enter group name" class="form-control name_list" /></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
        });
        $(document).on('click', '.btn_remove', function () {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });
        $('#submit').click(function () {
            $.ajax({
                url: "groupAdder.php",
                method: "POST",
                data: $('#group_name').serialize(),
                success: function (data) {
                    toggleAlert(data)
                    setTimeout(function () {
                        window.location = window.location.href.split("?")[0]; //Reloading without arguments
                    }, 5000);
                    $('#group_name')[0].reset();
                }
            });
        });
    });
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
                            window.location = window.location.href.split("?")[0]; //Reloading without arguments
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

    function toggleAlert(data) {
        $("#addMsg").html(data);
    }
</script>
