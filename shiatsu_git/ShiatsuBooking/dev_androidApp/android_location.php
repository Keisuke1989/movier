<?php
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];

$table = "locations";
$today = date("Y-m-j"); 
 
$resultArray = array(); 
   
$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//get the location data from db
$sql = "SELECT * FROM $table WHERE delete_flg = 0";
$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $resultArray[] = $row;
    }
} else {
    echo "{}";
}

echo json_encode($resultArray,JSON_FORCE_OBJECT);
$result->close();
$mysqli->close();

?> 