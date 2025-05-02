<?php

$idToken = $_POST['idToken']; 
$userMail = $_POST['userMail'];

require_once "utils/check_google_login.php";
if (checkGoogleLogin($idToken, $userMail) == false) 
    die(json_encode(["error" => "google check failed"]));

$name = $_POST["name"];
$phone = $_POST["phone"];

if (mb_strlen($name) > 25 || strlen($phone) > 15 || !is_numeric($phone))
    die(json_encode(["error" => "input problem"]));

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$conn->begin_transaction();

$query = "SELECT Mail FROM DeletedUser WHERE Mail = ?";
$res = runSelectQuery($conn, $query , [$userMail]);
if ($res === null)
    die(json_encode(["error" => "cmd failed : " .$query . " [" . $userMail . "]"]));

if ($res) //false is not fount user
{
    $query = "DELETE FROM DeletedUser WHERE Mail = ?";
    $res = runExecQuery($conn,$query,[$userMail]);
    if ($res != 1) 
        die(json_encode(["error" => "cmd failed : " .$query . " [" . $userMail . "]"]));
}    

require_once "utils/generate_uuid.php";
$secretKey = generateUuid();

$query = "INSERT INTO User (Mail, Name, Phone, SecretKey, Block) VALUES (?, ?, ?, ?, 0)";
$values = [$userMail, $name, $phone, $secretKey];
$usersInsert = runExecQuery($conn,$query,$values);

if ($usersInsert == 1) 
{
    require_once "../getMsgFromManager.php";
    $msg = getMsgFromManager($conn);
    if ($msg === null)
        die(json_encode(["error" => "cmd failed : get massage from manager"])); 
    require_once "../send_mail.php";
    $mailSended = sendSingleMailWithoutBody($userMail, "ברוך הבא למספרה");
    if (! $mailSended)
        die(json_encode(["error" => "cmd failed : send mail failed"])); 
    $conn->commit();
    $userData = 
    [
        "userName" => $name,
        "userPhone" => $phone,
        "managerMsg" => $msg,
        "secretKey" => $secretKey,
        "userMail" => $userMail
    ];
    echo json_encode($userData);
} 
else 
    die(json_encode(["error" => "cmd failed " . $query . $values]));

?>
