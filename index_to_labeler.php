<?php
session_start();
if (!empty($_POST["username"])) {
    $_SESSION["username"] = $_POST["username"];
}
header(
  "Location: labeler.php?location=" . $_POST["location"] . "&sample=0"
);
exit;