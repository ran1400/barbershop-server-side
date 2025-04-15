<?php

function checkIfBlockedUser($conn,$userMail)
{
    $query = "SELECT Block FROM User WHERE Mail = ?";
    $res = runSelectQuery($conn,$query,[$userMail]); //from require_once __DIR__ . "/../../sql.php";
    if (! $res)
       die(json_encode(["error" => "cmd failed : " . $query]));
    if ($res['Block'])
        return true;
    else
        return false;
}
?>