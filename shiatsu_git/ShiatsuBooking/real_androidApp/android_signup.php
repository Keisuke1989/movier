<?php
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];
$table = "customers";
$today = date("Y-m-j H:i:s"); 
date_default_timezone_set('PDT');

$resultArray = array(); 

$input = file_get_contents("php://input");
$ar[] = explode(",", $input);
$username = $ar[0][0];
$email = $ar[0][1]; 
$phone = $ar[0][2];

$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//Check for duplicate customers
$sql = "SELECT * FROM $table WHERE name = '$username' AND email = '$email' AND tel = '$phone' AND delete_flg = 0";
$result = mysqli_query($conn, $sql);
if ($result->num_rows > 0) {
   while($row = $result->fetch_assoc()) {
        $resultArray[] = $row;

    //     $username_forSQL = $row['name'];
    //     $userid_forSQL = $row['id'];
    //     $userno_forSQL = $row['no'];
    // 
      }
 // echo json_encode("{\"0\":{\"id\":\"$userid_forSQL\",\"no\":\"$userno_forSQL\",\"name\":\"$username_forSQL\"}}",JSON_FORCE_OBJECT);
     echo json_encode($resultArray,JSON_FORCE_OBJECT);
} else {
    $sql = "SELECT * FROM $table WHERE email= '$email' AND delete_flg = 0";
    $result = mysqli_query($conn,$sql);
    
    if($result->num_rows > 0){
      $resultString = array('error' => "2");
      $resultArray = '{"0" : {"Duplicate Error":"1"}}';
      echo json_encode($resultString,JSON_FORCE_OBJECT);
    }else{
         $sql = "INSERT INTO $table (`id`, `no`, `name`, `email`, `email_verified`, `email_token`, `email_token_expires`, `password`, `password_token`, `tel`, `remarks`, `point`, `created`, `delete_flg`) VALUES (NULL, '', '$username', '$email', '0', NULL, NULL, NULL, NULL, '$phone', NULL, '0', '$today', '0')";
        if ($conn->query($sql) === TRUE){
          $sql = "SELECT * FROM $table WHERE name = '$username' AND email = '$email' AND tel = '$phone'";
          $result = mysqli_query($conn, $sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $resultArray[] = $row;
                }
                echo json_encode($resultArray,JSON_FORCE_OBJECT);
            } else {
                echo "{}";
            }
        } else {
          $resultString = array('error' => "1");
            // echo "{\"0\":{\"error\":\"1\"}}";
          echo json_encode($resultString,JSON_FORCE_OBJECT);
        }

    }
   
}

$result->close();
$mysqli->close();
?> 