<?php
session_start();
if (!empty($_POST["username"])) {
    $_SESSION["username"] = $_POST["username"];
}
header(
  "Location: labeler.php?sample_type=" . $_POST["sample_type"] . "&location=" . $_POST["location"] . "&sample=0"
);
exit;
exit;