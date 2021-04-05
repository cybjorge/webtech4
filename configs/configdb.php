<?php

function connect(){
    $conn=null;

    $servername = "localhost:3306";
    $username = "xbalascik";
    $password = "pRc48e9$#T89wB";
    $dbname = "z4";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function last_commit_in_db($commit,$conn){
    echo $commit."\n";
    $query="SELECT * FROM lastcommit WHERE etag='$commit'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 0) {
        return 1;
    }
    else{return 0;};
}
?>