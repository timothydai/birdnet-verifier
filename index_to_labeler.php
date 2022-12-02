<?php
session_start();
if (!empty($_POST["username"])) {
  $_SESSION["username"] = $_POST["username"];
}

if ($_POST["submit"] === "Start labeling!") {
  if (isset($_POST["location"])) {
    header(
      "Location: labeler.php?location=" . $_POST["location"] . "&sample=0"
    );
  } else {
    header(
      "Location: labeler.php?sample=0"
    );
  }
} else {
  header(
    "Location: records.php"
  );
}
exit;
