<?php
 
  $url = "50.23.97.44";
  $user = "ckmsol_shiatsu";
  $pass = "gc6Gy97!";
  $db = "ckmsol_shiatsu";
  $table = "work_shifts";
  $today = date("Y-m-j"); 
  $today = "2016-04-16";
  $startTime="";
  $serviceID="";
  $serviceDuration="";
  $bufferTime = "00:15:00";
  $resultArray = array(); 
  $input = file_get_contents("php://input");
   
  $i =0;
  $location_id=$input;//タブレットからロケーションIDを取得取得

 
 $mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");

 $conn = mysqli_connect($url, $user, $pass, $db);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
 $sql = "SELECT * FROM $table WHERE day = '$today' AND location_id = $location_id ORDER BY `work_shifts`.`next_available_time` ASC";

$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $resultArray[$i] = array (
            'id' => $row['id'],
            'day' => $row['day'],
            'therapist_id' => $row['therapist_id'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'location_id' => $row['location_id'],
            'update_flg' => $row['updata_flg'],
            'next_available_time' => $row['next_available_time']
          );
        $i++;
    }
} else {
    echo "0 results";
}

$startTime = substr($resultArray[0]["next_available_time"],0,5);

echo $startTime;

  // header('Content-Type: application/json; charset=utf-8'); //JSONファイルの出力
// echo json_encode($jsonString, JSON_UNESCAPED_UNICODE); 


 // echo $username+"\n"+"test"+"\n";
    $result->close();
   $mysqli->close();

?> 