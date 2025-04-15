<?php

require_once "../sql.php";
$conn = getConn();

if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"])); 
else
{
    $userMail = $_POST["userMail"]; 
    $query = "SELECT SecretKey,Name,Phone,Block FROM User WHERE Mail = ?";
    $userDetails = runSelectQuery($conn,$query,[$userMail]);
    if ($userDetails === null)
        die(json_encode(["error" => "cmd failed : ".$query]));
    if ($userDetails === false)
        die(json_encode(["newUser" => "V"]));
    $userSecretKey = $userDetails['SecretKey'];
    $userName = $userDetails['Name'];
    $userPhone = $userDetails['Phone'];
    $userIsBlocked = $userDetails['Block'];
    require "utils/check_google_login.php";
    if (checkGoogleLogin($_POST["idToken"],$userMail) == false)
        die(json_encode(["error" => "wrong token"]));
    if($userIsBlocked)
        die(json_encode(["userIsBlocked" => "v"]));
    require "utils/get_crnt_queue.php";
    $queue = getCrntQueue($userMail,$conn);
    if($queue === null)
        die(json_encode(["error" => "cmd failed : get crnt queue"]));
    require "utils/msg.php";
    $userData = 
    [
        "userName" => $userName,
        "userPhone" => $userPhone,
        "managerMsg" => $msg,
        "userMail" => $userMail,
        "userSecretKey" => $userSecretKey
    ];
    if($queue != false)
        $userData["userQueue"] = $queue;
    $json = json_encode($userData);
    echo $json;
}

?>