<?php

function toSendUserUnblockNotification($conn)
{
    $query = "SELECT SendUserUnblockNotification FROM Setting";
    $res = mysqli_query($conn, $query);
    if (! $res)
        return null;
    $res = mysqli_fetch_assoc($res);
    if (! $res)
        return null;
    return $res["SendUserUnblockNotification"];
}

?>