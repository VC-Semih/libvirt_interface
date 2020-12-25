<?php
require_once('tools.php');
global $api_location;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

require('header.php');
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/3.1.2/plupload.full.min.js"></script>';
echo "<div class='container mt-4'>";
if ($action) {
    if ($action === 'volume-delete') {
        $url = "http://" . $api_location . "/api/vm/volume.php?name=" . $_GET['name'];

        $response = httpDelete($url);
        $response = json_decode($response, true);
        if ($response["status"] === 1) {
            function_alert('alert-success', "Success", $response['status_message']);
        } else if ($response["status"] === 0) {
            function_alert('alert-danger', "Error", $response['status_message']);
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
        $volumes = []; //Emptying the array so foreach doesn't start
    }
}

echo "<h1>Default storage pool volumes</h1>";
echo "<div class='container'>" .
    "<table class='table mt-5 table-responsive'>" .
    "<thead class='table-dark'>" .
    "<tr>" .
    "<th>Name</th>" .
    "<th>Type</td>" .
    "<th>Capacity</th>" .
    "<th>Allocation</th>" .
    "<th>Path</th>" .
    "<th>Actions</th>" .
    "</tr>" .
    "</thead>" .
    "<tbody class='table-dark'>";
foreach ($volumes as $volume) {
    echo
        "<tr>" .
        "<td>" . $volume['volume_name'] . "</td>" .
        "<td>" . $volume['volume_type'] . "</td>" .
        "<td>" . $volume['volume_capacity'] . "</td>" .
        "<td>" . $volume['volume_allocation'] . "</td>" .
        "<td>" . $volume['volume_path'] . "</td>" .
        "<td><div id='actions_" . $volume['volume_name'] . "'>" ?><a type='button' style='color: black'
                                                                     class='btn btn-light m-1' href='#'
                                                                     onclick='makeVisibleForm("<?php echo $volume["volume_name"] ?>")'>
        Clone volume</a>
    <?php echo "<a type='button' class='btn btn-danger m-1' href=\"?action=volume-delete&name=" . $volume['volume_name'] . "\">Delete volume</a></div>";
    echo
        "<form class='form' method='POST' id='" . $volume['volume_name'] . "'></form></td>" .
        "</tr>";
}
echo "</tbody>" .
    "</table>";

echo '<button id="pickfiles" type="button" class="btn btn-dark">Send image disk file</button>' .
    '<div id="filelist">Your browser doesn\'t support HTML5 upload.</div>' .
    '</div>' .
    '</div>' .
    '</body>' .
    '</html>';
?>
<script type="text/javascript">
    window.addEventListener("load", function () {
        const uploader = new plupload.Uploader({
            runtimes: "html5,html4",
            browse_button: "pickfiles",
            url: '<?php echo("http://" . $api_location . "/api/vm/volume.php"); ?>',
            chunk_size: "255mb",
            multiple_queues: true,

            filters: {
                mime_types: [{title: "Image disk", extensions: "qcow2,raw"}]
            },
            init: {
                PostInit: function () {
                    document.getElementById("filelist").innerHTML = "";
                },
                FilesAdded: function (up, files) {
                    plupload.each(files, function (file) {
                        document.getElementById("filelist").innerHTML += `<div id="${file.id}" class="mt-4 ml-3 mr-1 col-10 border border-dark rounded" style="text-align: center;">${file.name} (${plupload.formatSize(file.size)}) <div class="progress"></div></div>`;
                    });
                    uploader.start();
                },
                UploadProgress: function (up, file) {
                    document.querySelector(`#${file.id} div`).innerHTML = `
                    <div class="progress-bar bg-dark" role="progressbar" style="width: ${file.percent}%" aria-valuenow="${file.percent}" aria-valuemin="0" aria-valuemax="100">${file.percent}%</div>
                `;
                    if (file.percent === 100)
                        document.querySelector(`#${file.id} div`).innerHTML = `
                    <div class="progress-bar bg-success" role="progressbar" style="width: ${file.percent}%" aria-valuenow="${file.percent}" aria-valuemin="0" aria-valuemax="100">Done !</div>
                `;
                },
                Error: function (up, err) {
                    console.log(err);
                },
                UploadComplete: function (up, files) {
                    //setTimeout(location.reload.bind(location), 2000);
                }
            }
        });
        uploader.init();
    });

    function makeVisibleForm(volume_name) {
        document.getElementById(volume_name).innerHTML = `
        <div class="form-group">
            <label for="new_volume_name">Volume name:</label>
            <input type="text" required class="form-control" name="new_volume_name" id="new_volume_name">
            <input type="text" style="visibility: hidden" value="${volume_name}" name="volume_name">
            <button type="submit" class="btn btn-info mt-1">Clone</button>
            </div>`;
    }


    $('.form').submit(function (event) {

        // Stop form from submitting normally
        event.preventDefault();

        // Get some values from elements on the page:
        let $form = $(this),
            new_volume_name = $form.find("input[name='new_volume_name']").val(),
            volume_name = $form.find("input[name='volume_name']").val(),
            url = '<?php echo('http://' . $api_location . '/api/vm/volume.php'); ?>'
        // Send the data using post
        $.post(url,
            {
                action: 'clone',
                new_volume_name: new_volume_name,
                volume_name: volume_name,
                timeout: 5000

            }, function (data) {
                console.log(data['status_message'])
            }
        );
        const id = Math.floor(Math.random() * 100)
        const cloneUrl = '<?php echo('http://' . $api_location . '/api/vm/volume.php?filename='); ?>' + volume_name + new_volume_name + 'copyprogress.txt';
        document.getElementById('actions_' + volume_name).innerHTML = '';
        document.getElementById(volume_name).innerHTML = `<div id="${id}" class="mt-4 ml-3 mr-1 col-10 border border-info rounded" style="text-align: center;">Copying ${volume_name} to ${new_volume_name}<div class="progress mb-2"></div></div>`;

        getCloneProgress(cloneUrl, 0, 0, id);

    });

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