<?php
session_start();
if (isset($_POST["submit"])) {
  if (isset($_POST["username"])) {
    $_SESSION["username"] = $_POST["username"];
  }
  if (isset($_POST["sample_type"]) and isset($_POST["location"]) and isset($_SESSION["username"])) {
    header(
      "Location: labeler.php?sample_type=" . $_POST["sample_type"] . "&location=" . $_POST["location"] . "&sample=0"
    );
    exit;
  } else {
    echo "<script>alert('Fill in all fields.');</script>";
  }
}
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
  <div style="text-align:center">
    <form action="" method="post">
      <div style="margin-bottom:15px">
        <?php if (empty($_SESSION["username"])) { ?>
          <div>Name: <input type="text" name="username" autocomplete="off"></div>
        <?php } else { ?>
          Welcome back, <?php echo $_SESSION["username"]; ?>. <a href=<?php session_destroy(); ?>>I am not <?php echo $_SESSION["username"]; ?>.</a>
        <?php } ?>
      </div>
      <div style="margin-bottom:15px">
        <div>Select Sample Type:</div>
        <input type="radio" id="random" name="sample_type" value="random">
        <label for="random">Random Sample only</label>
        <input type="radio" id="high" name="sample_type" value="randomhighconfidence">
        <label for="high">High Confidence Sample only</label>
      </div>

      <div style="margin-bottom:15px">
        <div>Select Data Source:</div>
        <input type="radio" id="bat" name="data_source" value="bat">
        <label for="bat">Bat Monitoring Recorders</label>
        <input type="radio" id="video" name="data_source" value="video">
        <label for="video">Video Cameras</label>
      </div>

      <div style="margin-bottom:15px">
        <div>Select Location:</div>
        <div id="location_instructions">Select a data source first.</div>
        <div id="bat_locations" style="display: none;">
          <div><img src="static_images/bat_recorder_map.png" width="800"></div>
          <input type="radio" id="all_bat_locs" name="location" value="all_bat_locs">
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
        </div>

        <div id="cam_locations" style="display: none;">
          <div><img src="static_images/video_camera_map.png" width="800"></div>
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
        </div>
      </div>
      <input type="submit" name="submit" value="Start Labeling">
    </form>
  </div>
</body>

</html>