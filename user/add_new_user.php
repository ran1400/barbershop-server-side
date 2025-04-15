<?php

$idToken = $_POST['idToken']; 
$userMail = $_POST['userMail'];

require_once "utils/check_google_login.php";
if (checkGoogleLogin($idToken, $userMail) == false) 
    die(json_encode(["error" => "google check failed"]));

$name = $_POST["name"];
$phone = $_POST["phone"];

if (strpos($name,"<") !== false || strpos($name,"'") !== false || mb_strlen($name) > 25 || strlen($phone) > 15 || !is_numeric($phone))
    die(json_encode(["error" => "input problem"]));


require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$conn->begin_transaction();

$cmd = "SELECT Mail FROM DeletedUser WHERE Mail = ?";
$res = runSelectQuery($conn, $cmd , [$userMail]);
if ($res === null)
    die(json_encode(["error" => "cmd failed : " .$cmd . " [" . $userMail . "]"]));


if ($res != false) //false is not fount user
{
    $cmd = "DELETE FROM DeletedUser WHERE Mail = ?";
    $res = runExecQuery($conn,$cmd,[$userMail]);
    if ($res != 1)
        die(json_encode(["error" => "cmd failed : " .$cmd . " [" . $userMail . "]"]));
}    

require_once "utils/generate_uuid.php";
$secretKey = generateUuid();

$cmd = "INSERT INTO User (Mail, Name, Phone, SecretKey, Block) VALUES (?, ?, ?, ?, 0)";
$values = [$userMail, $name, $phone, $secretKey];
$usersInsert = runExecQuery($conn,$cmd,$values);
if ($usersInsert == 1) 
{
    $conn->commit();
    require_once "../send_mail.php";
    sendSingleMailWithoutBody($userMail, "ברוך הבא למספרה");
    require_once "utils/msg.php";

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
    die(json_encode(["error" => "cmd failed " . $cmd . $values]));

?>
