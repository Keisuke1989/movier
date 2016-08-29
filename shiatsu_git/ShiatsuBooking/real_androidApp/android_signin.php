<?php
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];
$table = "customers";
$resultArray = array(); 
date_default_timezone_set('Canada/Pacific');
$customerNo = file_get_contents("php://input");

$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//check if the customer no exits or not
$sql = "SELECT * FROM $table WHERE no = '$customerNo' AND delete_flg = 0";
$result = mysqli_query($conn, $sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $resultArray[] = $row;
    }
    echo json_encode($resultArray,JSON_FORCE_OBJECT);
} else {
    echo "{}";
}

$result->close();
$mysqli->close();

?> 