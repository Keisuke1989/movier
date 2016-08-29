<?php
 require_once(dirname(__FILE__) . '/android_functions.php');
  $url = $dbInfo_array['url'];
  $user = $dbInfo_array['user'];
  $pass = $dbInfo_array['pass'];
  $db = $dbInfo_array['dbname'];

  $table = "treatment_checkins";
  $today = date("Y-m-j H:i:s"); 
  date_default_timezone_set('Canada/Pacific');
  $resultArray = array(); 
  $input = file_get_contents("php://input");
  $ar[] = explode(",", $input);
  $startTime = $ar[0][0];
  $customerId = $ar[0][1];
  $serviceId = $ar[0][2];
  $therapistId = $ar[0][3];
  $locationId = $ar[0][4];
  
  $serviceDuration = "";
  $nextAvailable = "";
  $interval = 0;
  $today_date = date("Y-m-j");


 $mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
 $conn = mysqli_connect($url, $user, $pass, $db);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//get the interval time from db
$sql = "SELECT value from configs WHERE id = 6";
$result = mysqli_query($conn, $sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $interval = $row['value'];
    }
}
mysqli_free_result($result);

//get the servise duration from db
$sql = "SELECT duration from services WHERE id = $serviceId AND delete_flg = 0";
$result = mysqli_query($conn, $sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $serviceDuration = $row['duration'];
    }
}
mysqli_free_result($result);
$addtime = $serviceDuration + $interval;
$nextAvailable = date('H:i:s', strtotime("+ $addtime minutes", strtotime($startTime)));


//Update the next available time to db
$sql = "UPDATE work_shifts SET next_available_time = '$nextAvailable' WHERE therapist_id = $therapistId AND day = '$today_date' AND location_id='$locationId'";

if ($conn->query($sql) === TRUE){
    echo json_encode("The record updated successfully",JSON_FORCE_OBJECT);
} else {
    echo "{}";
}

//Insert the treatment info to db
$sql = "INSERT INTO $table (`id`, `day`, `start_time`, `customer_id`, `service_id`, `therapist_id`, `location_id`, `status_cd`) VALUES (NULL, '$today', '$startTime', '$customerId', '$serviceId', '$therapistId', '$locationId', '3001')";

if ($conn->query($sql) === TRUE){
    echo json_encode("New record created successfully",JSON_FORCE_OBJECT);
} else {
    echo "{}";
}

// $result->close();
$mysqli->close();

?>