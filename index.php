<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>JRBP Audio Labeler</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="scripts/filter_locations.js"></script>
</head>

<body>
  <div style="display:flex;justify-content:center;">
    <div style="width:800px;">
      <form action="index_to_labeler.php" method="post">
        <div style="margin-bottom:15px">
          <?php if (!isset($_SESSION["username"])) { ?>
            <div>
              <span style="font-weight:bold">Labeler Name:</span>
              <input type="text" name="username" autocomplete="off" required>
            </div>
          <?php } else { ?>
            Welcome back, <?php echo $_SESSION["username"]; ?>. <a href="destroy_session.php">I am not <?php echo $_SESSION["username"]; ?>.</a>
          <?php } ?>
        </div>

        <div style="margin-bottom:15px">
          <div style="font-weight:bold">Select Sample Type:</div>
          <input type="radio" id="random" name="sample_type" value="random" required>
          <label for="random">Random samples</label>
          <input type="radio" id="high" name="sample_type" value="randomhighconfidence">
          <label for="high">High confidence samples</label>
        </div>

        <div style="margin-bottom:15px">
          <div style="font-weight:bold">Select Data Source:</div>
          <input type="radio" id="bat" name="data_source" value="bat" required>
          <label for="bat">Bat Monitoring Recorders</label>
          <input type="radio" id="video" name="data_source" value="video">
          <label for="video">Video Cameras</label>
        </div>

        <div style="margin-bottom:15px">
          <div style="font-weight:bold">Select Location:</div>
          <div id="location_instructions">Select a data source first.</div>
          <div id="bat_locations" style="display: none;">
            <input type="radio" id="all_bat_locs" name="location" value="all_bat_locs" required>
            <label for="all_bat_locs">All bat locations</label>

            <input type="radio" id="lake1" name="location" value="lake1">
            <label for="lake1">Lake 1</label>

            <input type="radio" id="lake2" name="location" value="lake2">
            <label for="lake2">Lake 2</label>

            <input type="radio" id="marsh1" name="location" value="marsh1">
            <label for="marsh1">Marsh 1</label>

            <input type="radio" id="marsh2" name="location" value="marsh2">
            <label for="marsh2">Marsh 2</label>

            <input type="radio" id="upperlake1" name="location" value="upperlake1">
            <label for="upperlake1">Upper Lake 1</label>

            <input type="radio" id="ridge1" name="location" value="ridge1">
            <label for="ridge1">Ridge 1</label>

            <input type="radio" id="barn1" name="location" value="barn1">
            <label for="barn1">Barn 1</label>
            <div style="margin-top:15px"><img src="static_images/bat_recorder_map.png" style="width:100%"></div>
          </div>

          <div id="cam_locations" style="display:none;">
            <input type="radio" id="all_cam_locs" name="location" value="all_cam_locs">
            <label for="all_cam_locs">All cam locations</label>

            <input type="radio" id="cam01" name="location" value="cam01">
            <label for="cam01">Cam01</label>

            <input type="radio" id="cam02" name="location" value="cam02">
            <label for="cam02">Cam02</label>

            <input type="radio" id="cam04" name="location" value="cam04">
            <label for="cam04">Cam04</label>

            <input type="radio" id="cam07" name="location" value="cam07">
            <label for="cam07">Cam07</label>

            <input type="radio" id="cam09" name="location" value="cam09">
            <label for="cam09">Cam09</label>

            <input type="radio" id="cam10" name="location" value="cam10">
            <label for="cam10">Cam10</label>
            <div style="margin-top:15px"><img src="static_images/video_camera_map.png" style="width:100%"></div>
          </div>
        </div>
        <input type="submit" name="submit" value="Start labeling!">
      </form>
    </div>
  </div>
</body>

</html>