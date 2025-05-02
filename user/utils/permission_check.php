<?php

function permissionCheck($conn,$userMail,$userSecretKey)
{
    $query = "SELECT SecretKey FROM User WHERE Mail = ?";
    $res = runSelectQuery($conn, $query , [$userMail]); //from sql.php
    if ($res === null) 
        return null;
    if ($res === false)
        return false;
    if(hash_equals($res["SecretKey"],$userSecretKey))
        return true;
    else
        return false;
}

?>