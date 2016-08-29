<?php

// DB Information
require_once(dirname(__FILE__) . '/android_functions.php');
$url = $dbInfo_array['url'];
$user = $dbInfo_array['user'];
$pass = $dbInfo_array['pass'];
$db = $dbInfo_array['dbname'];

//table Info
$table = "work_shifts";
$table2 = "users";

// $current_time = date("H:i:s", strtotime("+5 min"));
$current_time = date("H:i:s");
$current_time = roundUpTime($current_time);
$location_id = file_get_contents("php://input");

//defalt value
$resultArray = array();
$tableAvailableTime="";
$start_time = "";
$duration = "";
$interval=0;
$tableNumber = 0;
$inTreatment = 0;
  
//DB Connection
$mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");
$conn = mysqli_connect($url, $user, $pass, $db);
if (!$conn) { die("Connection failed: " . mysqli_connect_error());}

$interval = getInterval($conn);
$tableNumber = getTableNumber($conn,$location_id,$delete_flg);
$min_duration = getMinDuration($conn,$location_id);
$min_interval = $interval + $min_duration;

/*
*	勤務している施術士数とテーブル数を比べテーブル数が大きい場合、
	テーブルは常に空いている状態なので、テーブル予約可能時間なしのデータを送信
*/

$sql = "SELECT * from work_shifts 
		WHERE day = CURDATE() 
		AND delete_flg = '0' 
		AND location_id = '$location_id'
		AND start_time <= '$current_time'
		AND end_time >= '$current_time'";

$thera_count = mysqli_query($conn, $sql);

if($thera_count->num_rows >= $tableNumber){
	$therapist_available = CheckAvailablePerson($conn,$location_id,$current_time,$interval);
	sendJSONtoAndroid($conn,$location_id,$interval,$therapist_available);
	$result->close();
	$mysqli->close();
	exit;
}

//get number of treatment which starts within 20min(shortest session time + interval) and which is in treatment

$sql = "SELECT * from treatment_checkins INNER JOIN services on treatment_checkins.service_id = services.id
	where treatment_checkins.location_id = '$location_id' 
	AND treatment_checkins.day = CURDATE()
	AND treatment_checkins.status_cd != '3999'
	AND treatment_checkins.delete_flg = '0'
	AND (
			(
                treatment_checkins.start_time <= ADDTIME(CURTIME(),SEC_TO_TIME(5*60))
                AND 
                ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60))>= ADDTIME(CURTIME(),SEC_TO_TIME(5*60))
            )
			OR 
			(treatment_checkins.start_time >= ADDTIME(CURTIME(),SEC_TO_TIME(5*60)) AND treatment_checkins.start_time <= ADDTIME( ADDTIME(CURTIME(),SEC_TO_TIME(5*60)) , SEC_TO_TIME($min_interval*60)))
		)
	ORDER BY ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) DESC";

	$result = mysqli_query($conn, $sql);
	$laterTreatment = $result->num_rows;
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) { $resultArray = $row; }

			//Compare number of treatments and number of tables
			if($laterTreatment > 0 && $laterTreatment >= $tableNumber){

				$tableAvailableTime = CulcTableAvailableTime($resultArray['start_time'],$resultArray['duration'],$interval);
				$tableAvailableTime = LoopCheckTableAvailable($conn,$location_id,$tableAvailableTime,$min_interval,$tableNumber,$interval);
				$therapist_available = CheckAvailablePerson($conn,$location_id,$tableAvailableTime,$interval);
				sendJSONtoAndroidWithTableTime(
					$conn,
					$location_id,
					$interval,
					$tableAvailableTime,
					$therapist_available
				);
			}else{
				
				$therapist_available = CheckAvailablePerson($conn,$location_id,$current_time,$interval);
				 sendJSONtoAndroid($conn,$location_id,$interval,$therapist_available);
			}
	} else {
		$therapist_available = CheckAvailablePerson($conn,$location_id,$current_time,$interval);
		 sendJSONtoAndroid($conn,$location_id,$interval,$therapist_available);
	}

$result->close();
$mysqli->close();

?> 