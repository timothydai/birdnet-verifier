<?php
  $connect = mysqli_connect("db4free.net", "timdai", "172cb102", "birdnet_ids", "3306");
  $detection_id = $_GET["sample"];
  $sample = mysqli_query($connect, "SELECT * FROM birdnet_detections WHERE id = $detection_id;")->fetch_assoc();

  $post_url = "send_post.php?sample=" . $detection_id;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Labeler - Bat Monitoring Recorders</title>
  </head>
  <body>
    <audio controls src=<?php echo $sample["uri"] ?>></audio>
    <div>Location: <?php echo $sample["location"] ?></div>
    <div>Filename: <?php echo $sample["filename"] ?></div>
    <div>Datetime: 2013-07-01 05:20:15</div>
    <div>
        <form action=<?php echo $post_url ?> method="post">
            <div>Enter species:</div>
            <input type="text" name="species_common_name">
            <div>Additional notes: (Is there anything unique about this recording?)</div>
            <textarea name="comments" rows="4" cols="100"></textarea>
            <div></div>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>
    <div>
        <a href="bat_monitoring_recorders.html">Back to Bat Recordings Home</a>
        |
        <a href="index.html">Back to Labeler Home</a>
    </div>
  </body>
</html>