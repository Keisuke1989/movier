<?php
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];
$table = "customers";
$today = date("Y-m-j H:i:s"); 
date_default_timezone_set('Canada/Pacific');
$resultArray = array(); 
$resultString ="";
$username = file_get_contents("php://input");
   
$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "INSERT INTO $table (`id`, `no`, `name`, `email`, `email_verified`, `email_token`, `email_token_expires`, `password`, `password_token`, `tel`, `remarks`, `point`, `created`, `delete_flg`) VALUES (NULL, '', '$username', '', '0', NULL, NULL, NULL, NULL, '', NULL, '0', '$today', '0')";

if ($conn->query($sql) === TRUE){
    $sql = "SELECT LAST_INSERT_ID()";
    //$sql = "SELECT id FROM $table WHERE name = '$username'";
    $result = mysqli_query($conn, $sql);
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $resultString = $row["LAST_INSERT_ID()"];
      }
      $resultString = array("0" => array("id" => $resultString));
      echo json_encode($resultString,JSON_FORCE_OBJECT);
    } else {
      echo "{}";
    }
} else {
    echo "{}";
}

$result->close();
$mysqli->close();
?> 