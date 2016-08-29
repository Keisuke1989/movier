<?php
 
  $url = "50.23.97.444";
  $user = "ckmsol_shiatsu";
  $pass = "gc6Gy97!";
  $db = "ckmsol_shiatsu";
  $table = "customers";
  
  if(!isset(urldecode($_POST['name']))){
    $username = "nothing";
  }else{
    $username = urldecode($_POST['name']);)
  }
  $email = $_GET['email'];
  $phone = $_GET['phone'];

 
 $mysqli = new mysqli($url,$user,$pass,$db) or die("Error : failed to connect to database");

 if($mysqli->connect_error){
 	echo $mysqli->connect_error;
 	exit();
 }else{
 	$mysqli->set_charset("utf8");
 }


 $sql = "SELECT * FROM customers";
  if($result = $mysqli->query($sql)){
  	 while ($row = $result->fetch_assoc()) {
  	 	 $users[] = array(
            'id'=> $row['id']
            ,'name' => $row['name']
            );
  	 }
  }

$ar[] = array("name" => $username); // "hoge" => "fuga"
$ar[] = array("email" => $email); // "aa" => "bb"

 $jsonString = "{\"name\":"+ $username + ",\"email\":" + $email + ",\"phone\": " + $phone + "}";

  // header('Content-Type: application/json; charset=utf-8'); //JSONファイルの出力
// echo json_encode($jsonString, JSON_UNESCAPED_UNICODE); 

echo "test\ntest\ntest";
 // echo $username+"\n"+"test"+"\n";
    $result->close();
   $mysqli->close();
?> 