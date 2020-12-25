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
    if ($action === 'template-delete') {
        $url = "http://" . $api_location . "/api/vm/template.php?name=" . $_GET['name'];

        $response = httpDelete($url);
        $response = json_decode($response, true);
        if ($response["status"] === 1) {
            function_alert('alert-success', "Success", $response['status_message']);
        } else if ($response["status"] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
        }
    }
}

echo "<h1>Templates: </h1>";
$response = httpGet('http://' . $api_location . '/api/vm/template.php');
$response = json_decode($response, true);
if (isset($response['status'])) {
    if ($response['status'] === 2) {
        function_alert('alert-warning', "Warning", $response['status_message']);
    }
} else {
    foreach ($response as $template) {
        echo '<h2 class="mt-5 ml-3">' . $template['template_name'] . ':</h2>';
        echo "<table class='table ml-5 table-responsive'>" .
            "<thead class='table-dark'>" .
            "<tr>" .
            "<th>Volume name</th>" .
            "<th>Vcpus</td>" .
            "<th>Ram</th>" .
            "<th>Description</th>" .
            "<th>Actions</th>" .
            "</tr>" .
            "</thead>" .
            "<tbody class='table-dark'>";
        echo
            "<tr>" .
            "<td>" . $template['template_volume_name'] . "</td>" .
            "<td>" . $template['template_description']['template_cpu'] . "</td>" .
            "<td>" . $template['template_description']['template_ram'] . "</td>" .
            "<td style='word-break: break-word'>" . $template['template_description']['template_description'] . "</td>" .
            "<td><div id='actions_" . $template['template_name'] . "'>" ?> <a type='button' style='color: black' class='btn btn-light m-1' href='#'
                                                                              onclick='deployTemplate("<?php echo $template["template_name"] ?>")'>Deploy
            template</a> <?php echo "  <a type='button' class='btn btn-danger m-1' href=\"?action=template-delete&name=" . $template['template_name'] . "\">Delete template</a>"; ?>
        </div>
        <?php
        echo "</tr>";
    }
    echo "</tbody>" .
        "</table>";
}

echo '<a href="createtemplate.php" type="button" class="btn btn-dark">Create new template</a>';

echo "</div>" .
    "</body>";
?>
<script type="text/javascript">
    function deployTemplate(template_name) {
        let url = '<?php echo('http://' . $api_location . '/api/vm/template.php'); ?>'
        // Send the data using post
        $.post(url,
            {
                action: 'deploy',
                template_name: template_name,
                timeout: 5000

            }, function (data) {
                console.log(data['status_message'])
            }
        );
        const id = Math.floor(Math.random() * 100)
        const cloneUrl = '<?php echo('http://' . $api_location . '/api/vm/volume.php?filename='); ?>' + 'template_' + template_name + '.qcow2' + 'copyprogress.txt';
        document.getElementById('actions_' + template_name).innerHTML = '';
        document.getElementById('actions_' + template_name).innerHTML = `<div id="${id}" class="mt-4 ml-3 mr-1 col-10 border border-info rounded" style="text-align: center;">Deploying  ${template_name}<div class="progress mb-2"></div></div>`;

        getCloneProgress(cloneUrl, 0, 0, id);
    }

    function getCloneProgress(url, percentage = 0, retries = 0, id) {
        if (percentage === 100) {
            document.querySelector(`[id='${id}'] div`).innerHTML = `<div class="progress-bar bg-success" role="progressbar" style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">Done !</div>`;
        } else if (retries === 5) {
            document.querySelector(`[id='${id}'] div`).innerHTML = `<div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">Aborted !</div>`;
        } else {
            if (!isNaN(percentage)) {
                document.querySelector(`[id='${id}'] div`).innerHTML = `<div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">${percentage}%</div>`;
            }
            $.get(
                url, {}, function (data) {
                    if (data['status'] === 0) {
                        retries++;
                        setTimeout(function () {
                            getCloneProgress(url, 0, retries, id);
                        }, 2000);
                    } else {
                        setTimeout(function () {
                            getCloneProgress(url, parseInt(data['status_message']), retries, id)
                        }, 1000);
                    }
                }
            ).fail(function (xhr, status, error) {
                console.log(xhr, status, error)
                retries++;
                setTimeout(function () {
                    getCloneProgress(url, 0, retries, id);
                }, 2000);
            })
        }
    }
</script>