<?php
    // Connecting to sql db.
    $connect = mysqli_connect("db4free.net", "timdai", "172cb102", "birdnet_ids", "3306");
    $query_format = "INSERT INTO expert_ids VALUES (%d, %d, '%s', '%s', '%s', '%s');";
    // Sending form data to sql db.
    $query = sprintf(
        $query_format,
        0, $_GET["sample"], $_POST["species_common_name"], $_POST["comments"], "Timothy Dai", date("Y-m-d H:i:s")
    );
    mysqli_query($connect, $query);

    
    header("Location: labeling_interface.php?sample=". ($_GET["sample"] + 1));
    exit;
?>