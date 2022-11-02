<?php
    // Connecting to sql db.
    $connect = mysqli_connect("db4free.net", "timdai", "172cb102", "birdnet_ids");

    // $query_format = "INSERT INTO expert_ids VALUES (%d, %d, %s, %s, %s)"
    // // Sending form data to sql db.
    // mysqli_query(
    //     $connect,
    //     sprintf(
    //         $query_format,
    //         1, 1, $_POST["species_common_name"], $_POST["comments"], date("Y-m-d H:i:s")
    //     )
    // )
?>