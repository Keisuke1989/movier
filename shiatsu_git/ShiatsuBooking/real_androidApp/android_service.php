<?php
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];
$table = "services";
$table2 = "users";

$resultArray = array();
$start_time = array();
$input = file_get_contents("php://input");
$ar[] = explode(",", $input);
$location_id = $ar[0][0];
$requestFlg = $ar[0][1];
$therapist_id = $ar[0][2];
$availableTime = $ar[0][3].":00";


$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$interval = getInterval($conn);
$tableNumber = getTableNumber($conn,$location_id);
$max_duration = getMaxDuration($conn,$location_id);
$min_duration = getMinDuration($conn,$location_id);
$result = 0;
$service_diff = 0;
$service_diff_person = 0;
$serviceFlag = false;



while(1){
 
    $sql ="SELECT COUNT(*) FROM treatment_checkins INNER JOIN services on treatment_checkins.service_id = services.id
                WHERE treatment_checkins.location_id = '$location_id'
                AND treatment_checkins.day = CURDATE()
                AND treatment_checkins.delete_flg = '0'
                AND (
                            treatment_checkins.start_time <= ADDTIME('$availableTime',SEC_TO_TIME($service_diff*60))
                            AND 
                            ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) >= ADDTIME('$availableTime',SEC_TO_TIME($service_diff*60))
                        
                    )
                ORDER BY ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) DESC";

    $result = mysqli_query($conn, $sql);

    if($result <= $tableNumber || $service_diff >= $max_duration){
        break;
    }else{
        $service_diff += 5;
    }
}


$sql = "SELECT start_time FROM treatment_checkins
                WHERE location_id = '$location_id'
                AND day = CURDATE()
                AND therapist_id = '$therapist_id'
                AND delete_flg = '0'
                AND start_time >= '$availableTime'
                ORDER BY start_time ASC";

$result = mysqli_query($conn, $sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $start_time[] = $row['start_time'];
        }
}


if($result->num_rows > 0){
$base = strtotime('00:00:00');
$service_diff_person = strtotime($start_time[0]) - strtotime($availableTime) + $base;
$service_diff_person = date('H:i:s',$service_diff_person);
$service_diff_person = ToMin($service_diff_person);

    if($service_diff > $service_diff_person && $service_diff_person >= $min_duration){
        $service_diff = $service_diff_person;
        $serviceFlag = true;
    }
}
$service_diff -= $interval;


if($requestFlg == "true"){
        $requestCode = 4002;
}else{
        $requestCode = 4001;
}

$sql = "SELECT * FROM $table 
                WHERE shop_id REGEXP '$location_id'
                AND id != 9999 
                AND request_flg = '$requestCode' 
                AND delete_flg = 0
                AND duration <= '$service_diff'
                ORDER BY duration ASC";
    
$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $resultArray[] = $row;
    }
} else {
    echo "{}";
}

$resultArray = array_merge($resultArray, array('serviceFlag' => $serviceFlag)); 
echo json_encode($resultArray,JSON_FORCE_OBJECT);

$result->close();
$mysqli->close();


?> 