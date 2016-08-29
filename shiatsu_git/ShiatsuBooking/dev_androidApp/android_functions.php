<?php

$dbInfo_array = array(
    "url" => "50.23.97.44",
    "user" => "ckmsol_shiatsu",
    "pass" => "gc6Gy97!",
    "dbname" => "ckmsol_shiatsu"
);

$delete_flg = 0;
date_default_timezone_set('Canada/Pacific');
error_reporting(E_ERROR | E_WARNING | E_PARSE);



function getInterval($conn){
	$sql= "SELECT value FROM configs WHERE id = 6";
	$result = mysqli_query($conn, $sql);

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	        $interval = $row['value'];
	    }
	}else{
		$interval = false;
	}
	mysqli_free_result($result);
	return $interval;
}
function getMinDuration($conn,$location_id){


	$sql = "SELECT duration FROM services WHERE shop_id REGEXP '$location_id' AND delete_flg = 0 ORDER BY duration ASC LIMIT 1";
	$result = mysqli_query($conn, $sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$min_duration = $row['duration'];
		}
	}else{
		$min_duration = false;
	}
	mysqli_free_result($result);
	return $min_duration;

}

function getMaxDuration($conn,$location_id){
	
	$sql = "SELECT duration FROM services WHERE shop_id REGEXP '$location_id' AND delete_flg = 0 ORDER BY duration DESC LIMIT 1";
	$result = mysqli_query($conn, $sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$max_duration = $row['duration'];
		}
	}else{
		$max_duration = false;
	}
	mysqli_free_result($result);
	return $max_duration;
}

function getTableNumber($conn,$location_id){
	$sql = "SELECT tables FROM locations WHERE id = $location_id AND delete_flg = 0";
	$result = mysqli_query($conn, $sql);
	if ($result->num_rows > 0) {
    	while($row = $result->fetch_assoc()) {
        	$tableNumber = $row['tables'];
    	}
	}else{
		$tableNumber = false;

	}
	mysqli_free_result($result);	
	return $tableNumber;
}

function sendJSONtoAndroid($conn,$location_id,$interval,$therapistAvailable){
 	$sql = "SELECT work_shifts.id, users.username, work_shifts.therapist_id, work_shifts.next_available_time, work_shifts.end_time FROM work_shifts 
		 			INNER JOIN users ON work_shifts.therapist_id = users.id 
		 			WHERE work_shifts.day = CURDATE() 
		 			AND location_id = $location_id 
		 			AND work_shifts.start_time <= ADDTIME(CURTIME(),SEC_TO_TIME($interval*60))
		 			AND work_shifts.end_time >= ADDTIME(work_shifts.next_available_time,SEC_TO_TIME($interval*60))
		 			AND work_shifts.delete_flg = 0 
		 			ORDER BY work_shifts.next_available_time ASC";
		 			
	$result = mysqli_query($conn, $sql);
	$resultArray = array();

	if ($result->num_rows > 0) {
			$i = 0;
			$j = 0;
	    	while($row = $result->fetch_assoc()) {
	    		
	        	for($j = 0; $j < count($therapistAvailable);$j++){
	        		if($therapistAvailable[$j]['therapist_id'] === $row['therapist_id']){

	        			$temp = $therapistAvailable[$j]['therapistAvailable'];
	        			$current_time_5min = date("H:i:s", strtotime("+5 min"));
	        			$current = date("H:i:s");
	        			if(strtotime($temp) <= strtotime($current_time_5min)){
	        				$temp = $current;
	        			}
	        		}
	        	}
	        	
	        	
	        	$work_end = $row['end_time'];

	        	if(strtotime($temp) < strtotime($work_end)){
	        		$resultArray[$i]['id'] = $row['id'];
	        		$resultArray[$i]['username'] = $row['username'];
		        	$resultArray[$i]['therapist_id'] = $row['therapist_id'];
		        	$resultArray[$i]['next_available_time'] = $row['next_available_time'];
		        	if(strtotime($row['next_available_time']) > strtotime($temp)){
		        		$resultArray[$i]['next_available_time_actual'] = $row['next_available_time'];
		        	}else{
		        		$resultArray[$i]['next_available_time_actual'] = $temp;
		        	}
		        	
		        	$i++;
	        	}
	        
	    	}
		} else {
	    	echo "{}";
		}
		mysqli_free_result($result);
		$resultArray = originalSort($resultArray);
		echo json_encode($resultArray,JSON_FORCE_OBJECT);
}

function sendJSONtoAndroidWithTableTime($conn,$location_id,$interval,$tableAvailabelTime,$therapistAvailable){
		$sql = "SELECT work_shifts.id, users.username, work_shifts.therapist_id, work_shifts.next_available_time, work_shifts.end_time FROM work_shifts
					INNER JOIN users ON work_shifts.therapist_id = users.id 
					WHERE work_shifts.day = CURDATE() 
					AND location_id = '$location_id'
					AND work_shifts.start_time <= ADDTIME(CURTIME(),SEC_TO_TIME($interval*60))
					AND work_shifts.end_time >= ADDTIME('$tableAvailabelTime',SEC_TO_TIME($interval*60))
					AND work_shifts.delete_flg = 0 
					ORDER BY work_shifts.next_available_time ASC";
		

		$resultArray = array();	
		$result = mysqli_query($conn, $sql);
		
		if ($result->num_rows > 0) {
			$i = 0;
			$j = 0;

	    	while($row = $result->fetch_assoc()) {
	    		

	        	for($j = 0; $j < count($therapistAvailable);$j++){
	        		if($therapistAvailable[$j]['therapist_id'] === $row['therapist_id']){

	        			$temp = $therapistAvailable[$j]['therapistAvailable'];
	        			$current_time_5min = date("H:i:s", strtotime("+5 min"));
	        			$current = date("H:i:s");
	        			if(strtotime($temp) <= strtotime($current_time_5min)){
	        				$temp = $current;
	        			}
	        		}
	        	}
	        	
	     
	        	$work_end = $row['end_time'];

	        	if(strtotime($temp) < strtotime($work_end)){
	        		$resultArray[$i]['id'] = $row['id'];
	        		$resultArray[$i]['username'] = $row['username'];
		        	$resultArray[$i]['therapist_id'] = $row['therapist_id'];
		        	$resultArray[$i]['next_available_time'] = $row['next_available_time'];
		        	if(strtotime($row['next_available_time']) > strtotime($temp)){
		        		$resultArray[$i]['next_available_time_actual'] = $row['next_available_time'];
		        	}else{
		        		$resultArray[$i]['next_available_time_actual'] = $temp;
		        	}
		        	
		        	$i++;
	        	}


	    	}
		} else {
	    	echo "{}";
		}
		mysqli_free_result($result);
		$resultArray = originalSort($resultArray);
		$resultArray = array_merge($resultArray, array('tableTime' => $tableAvailabelTime)); 
		echo json_encode($resultArray,JSON_FORCE_OBJECT);	
}
function originalSort($resultArray){
	foreach( $resultArray as $key => $row ) {
			$tmp_id_array[$key] = $row["id"];
			$tmp_uname_array[$key] = $row["username"];
			$tmp_thid_array[$key] = $row["therapist_id"];
			$tmp_atime_array[$key] = $row["next_available_time_actual"];
	}

	array_multisort( 
		$tmp_atime_array,SORT_ASC,
		$tmp_id_array,SORT_ASC,SORT_NUMERIC,
		$resultArray
	);

	return $resultArray;
}
function CheckAvailablePerson($conn,$location_id,$tableAvailabelTime,$interval){

	$therapistArray = GetTherapist($conn,$location_id);
	$therapistAva = array();
	
	for($i = 0; $i < count($therapistArray); $i++){
		$therapistAva[$i]['therapist_id'] = $therapistArray[$i];
		$therapistAva[$i]['therapistAvailable'] = CheckAvailableTime($conn,$location_id,$therapistArray[$i],$tableAvailabelTime,$interval);
			
	}
	
	return $therapistAva;
}

function CheckAvailableTime($conn,$location_id,$therapist_id,$tableAvailableTime,$interval){

	$sql ="SELECT * FROM treatment_checkins INNER JOIN services on treatment_checkins.service_id = services.id
                WHERE treatment_checkins.location_id = '$location_id'
                AND treatment_checkins.day = CURDATE()
                AND treatment_checkins.delete_flg = '0'
                AND treatment_checkins.therapist_id = '$therapist_id'
                AND treatment_checkins.start_time <= '$tableAvailableTime'
                AND (
                		(services.duration IS NOT NULL AND '$tableAvailableTime' <= ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60))) 
                		OR 
                		(treatment_checkins.end_time IS NOT NULL AND '$tableAvailableTime' <= treatment_checkins.end_time)
                	)
                ORDER BY ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) DESC";

    $result = mysqli_query($conn, $sql);

    if($result->num_rows>0){
		while($row = $result->fetch_assoc()) { 
			if($row['end_time'] == null){
				$tableAvailableTime = _get_sum_time($row['start_time'],MinToTime($row['duration'] + 5));
			}else{
				$tableAvailableTime = $row['end_time'];
				
			}
		 }
	}
	mysqli_free_result($result);

		
		$sql ="SELECT * FROM treatment_checkins INNER JOIN services on treatment_checkins.service_id = services.id
				WHERE treatment_checkins.location_id = '$location_id'
				AND treatment_checkins.day = CURDATE()
				AND treatment_checkins.therapist_id = '$therapist_id'
				AND treatment_checkins.delete_flg = '0'
				AND treatment_checkins.start_time >= '$tableAvailableTime'
				ORDER BY treatment_checkins.start_time ASC";

		$result = mysqli_query($conn, $sql);
		$perTreatment = array();
		$i =0;
		$j=1;

		

		if ($result->num_rows > 0) {

			while($row = $result->fetch_assoc()) { 
				$perTreatment[$i]['start_time'] = $row['start_time'];
				
				if($row['end_time'] == null){
					$row['duration'] += 5;
					$perTreatment[$i]['end_time'] = _get_sum_time($row['start_time'],MinToTime($row['duration'])); 
				}else{
					$perTreatment[$i]['end_time'] = $row['end_time']; 
				}

				//$perTreatment[$i]['end_time'] = _get_sum_time($row['start_time'],MinToTime($row['duration'])); 
				$i++;
			}
			
			$diff = diffTime($tableAvailableTime,$perTreatment[0]['start_time']);
			//echo $perTreatment[1]['end_time'];
			// $diff = ToMin($diff);
			if($diff < 20){
				$tableAvailableTime = $perTreatment[0]['end_time'];
				
				while(1){
					if(isset($perTreatment[$j]['start_time'])){
						$diff = diffTime($tableAvailableTime,$perTreatment[$j]['start_time']);
					}else{
						break;
					}
					
					if($diff > 20 || strtotime($tableAvailableTime) > strtotime("23:00:00") || $j > 20){
						break;
					}
					
					$tableAvailableTime = $perTreatment[$j]['end_time'];
					
					$j++;
				}
			}
			
		}
	mysqli_free_result($result);
	return $tableAvailableTime;

}


function GetTherapist($conn,$location_id){
	$sql = "SELECT therapist_id FROM work_shifts
		WHERE day = CURDATE()
		AND location_id = '$location_id'
		AND delete_flg = '0'";
	$result = mysqli_query($conn, $sql);
	$resultArray = array();
	$i=0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) { $resultArray[$i] = $row['therapist_id']; $i++;}
	}
	mysqli_free_result($result);
	return $resultArray;
}

function CulcTableAvailableTime($start_time,$duration,$interval){
		$tableAvailableTime = _get_sum_time($start_time,"00:".$duration.":00");
		$tableAvailableTime = _get_sum_time($tableAvailableTime,"00:".$interval.":00");
		return $tableAvailableTime;
}

function LoopCheckTableAvailable($conn,$location_id,$tableAvailableTime,$min_interval,$tableNumber,$interval){

	while(1){
				$sql ="SELECT * FROM treatment_checkins INNER JOIN services on treatment_checkins.service_id = services.id
				WHERE treatment_checkins.location_id = $location_id
				AND treatment_checkins.day = CURDATE()
				AND treatment_checkins.status_cd != '3999'
				AND treatment_checkins.delete_flg = '0'
				AND (
						(
			                treatment_checkins.start_time <= '$tableAvailableTime'
			                AND 
			                ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) >= '$tableAvailableTime'
			            )
						OR 
						(
							treatment_checkins.start_time >= '$tableAvailableTime'
							AND
							treatment_checkins.start_time <= ADDTIME( '$tableAvailableTime' , SEC_TO_TIME('$min_interval'*60)))
					)
				ORDER BY ADDTIME(treatment_checkins.start_time , SEC_TO_TIME(services.duration*60)) DESC";

				$result = mysqli_query($conn, $sql);
				$num_treatment = $result->num_rows;
				
				if($num_treatment >= $tableNumber){
					$resultArray = array();
					if ($result->num_rows > 0) {
					    while($row = $result->fetch_assoc()) { $resultArray = $row; }
					}
					$tableAvailableTime = CulcTableAvailableTime($resultArray['start_time'],$resultArray['duration'],$interval);
				}else{					
					break;
				}
				mysqli_free_result($result);

		}
		mysqli_free_result($result);
		
		return $tableAvailableTime;

}




function ToMin($time){
	$tArry = explode(":",$time);
	$hour = $tArry[0]*60;
	$secnd = round($tArry[2]/60,2);
	$mins = $hour+$tArry[1]+$secnd;
	return $mins;	
}

function MinToTime($i_minute){
	$hour = floor($i_minute / 60);
	$minute = $i_minute % 60;
	return $hour . ":" .$minute.":00";
}

function diffTime($start, $end) {

	$s = htos($end)-htos($start);
	return $s / 60;
}
function htos($hours) {
       $t = explode(":", $hours);
       $h = $t[0];
       if (isset($t[1])) {
        $m = $t[1];
       } else {
        $m = "0";
       } 
       if (isset($t[2])) {
        $s = $t[2];
       } else {
        $s = "0";
       } 
       return ($h*60*60) + ($m*60) + $s;
}
function _get_sum_time($source_time, $add_time) {
    $source_times = explode(":", $source_time);
    $add_times = explode(":", $add_time);
    return date("H:i:s", mktime($source_times[0] + $add_times[0], $source_times[1] + $add_times[1], $source_times[2] + $add_times[2]));
}

function roundUpTime($time){

	$tmp = explode(":",$time);
	$hour = $tmp[0];
	$minutes = $tmp[1];

	$minutes = round($minutes / 5) * 5; 
	if ($minutes >= 60) {
  		$minutes = 0;
  		$hour++;
	}


	return  $hour.":".$minutes.":00";
	
}

?>