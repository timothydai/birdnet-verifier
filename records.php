<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$connect = mysqli_connect("159.89.149.97", "birdnetv_public", "birdnetrools!", "birdnetv_base", "3306");
$num_labeled = mysqli_query($connect, "SELECT COUNT(DISTINCT birdnet_detection_id) as num_labeled FROM expert_ids;")->fetch_assoc()["num_labeled"];
$num_clips = mysqli_query($connect, "SELECT COUNT(DISTINCT id) as num_clips FROM birdnet_detections;")->fetch_assoc()["num_clips"];

$birdnet_detections = mysqli_query($connect, "SELECT id, recording_location FROM birdnet_detections ORDER BY id LIMIT 10;")->fetch_all();


$ids_and_agreers_per_clip = mysqli_query(
    $connect,
    "SELECT id, recording_location, recording_datetime, ids, comments, agreers, most_recent_logged_date, comments
    FROM (SELECT birdnet_detection_id, agreers, max(logged_date) as most_recent_logged_date, ids, comments
FROM (SELECT birdnet_detection_id, GROUP_CONCAT(logged_user SEPARATOR '; ') AS agreers, logged_date, ids, comments
FROM (SELECT birdnet_detection_id, logged_user, logged_date, GROUP_CONCAT(species_common_name SEPARATOR '; ') AS ids, comments FROM expert_ids GROUP BY logged_date ORDER BY logged_date DESC) AS submissions
GROUP BY birdnet_detection_id, ids ORDER BY logged_date DESC) as agreed_submissions
GROUP BY birdnet_detection_id) as most_recent_agreed_submissions LEFT JOIN birdnet_detections ON birdnet_detections.id = most_recent_agreed_submissions.birdnet_detection_id ORDER BY recording_location, recording_datetime;",
)->fetch_all();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JRBP Audio Labeler</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        table,
        tr,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 3px;
            font-weight: normal;
            /* table-layout: fixed; */
        }

        table {
            width: 100%;
        }

        th {
            /* width: 80px; */
            font-weight: bold;
        }

        #verified_bubble {
            cursor: default;
        }

        #verified_bubble #verified_bubble_text {
            visibility: hidden;
        }

        #verified_bubble:hover #verified_bubble_text {
            visibility: visible;
        }
    </style>
</head>

<body>
    <div style="display:flex;justify-content:center;">
        <div>
            <div style="margin-bottom:15px;"><a href="index.php">Go back home</a></div>
            <table>
                <tr>
                    <th>Clip ID</th>
                    <th>Recording Location</th>
                    <th>Recording Datetime</th>
                    <th>Most Recent Label</th>
                    <th>Comments</th>
                    <th>Labeler Name(s)</th>
                    <th>Submission Datetime</th>
                    <th style="position:relative">
                        Verified
                        <span id="verified_bubble">&#9432;
                            <span id="verified_bubble_text" style="position:absolute;width:150px;top:102%;left:-50%;text-align:left;background-color:rgb(210,210,210);padding:5px;border-radius:5px;">
                                Reviewed by at least 1 other person, i.e., a total of 2 agreeing submissions.
                            </span>
                        </span>
                    </th>
                    <th>Label Link</th>
                </tr>
                <?php foreach ($ids_and_agreers_per_clip as $row) { ?>
                    <tr>
                        <td><?php echo $row[0]; ?></td>
                        <td><?php echo $row[1]; ?></td>
                        <td><?php echo $row[2]; ?></td>
                        <td><?php if ($row[3] === null) {
                                echo "";
                            } else {
                                echo $row[3];
                            } ?></td>
                        <td><?php if ($row[4] === null) {
                                echo "";
                            } else {
                                echo $row[4];
                            } ?></td>
                        <td><?php if ($row[5] === null) {
                                echo "";
                            } else {
                                echo $row[5];
                            } ?></td>
                        <td><?php if ($row[6] === null) {
                                echo "";
                            } else {
                                echo $row[6];
                            } ?></td>

                        <?php if ($row[5] === null) {
                            echo "<td style='text-align:center;background-color:RGB(0,0,0,0.5);'>N/A</td>";
                        } else if (count(explode("; ", $row[5])) >= 2) {
                            echo "<td style='text-align:center;background-color:RGB(0,255,0,0.5);'>YES</td>";
                        } else {
                            echo "<td style='text-align:center;background-color:RGB(255,0,0,0.5);'>NO</td>";
                        } ?></td>
                        <td style="text-align:center;"><a target="_blank" rel="noopener noreferrer" href="<?php echo 'labeler.php?sample=0&birdnet_detection_id=' . $row[0]; ?>">Link</a> </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>

</html>