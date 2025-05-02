<?php

function checkIfBlockedUser($conn,$userMail)
{
    //require_once __DIR__ . "../../sql.php"; this in comment because its import already
    $query = "SELECT Block FROM User WHERE Mail = ?";
    $res = runSelectQuery($conn,$query,[$userMail]);
    if (! $res || ! isset($res['Block']))
       return null;
    return $res['Block'];
}

?>