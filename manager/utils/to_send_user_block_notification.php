<?php

function toSendUserBlockNotification($conn)
{
    $query = "SELECT SendUserBlockNotification FROM Setting";
    $res = mysqli_query($conn, $query);
    if (! $res)
        return null;
    $res = mysqli_fetch_assoc($res);
    if (! $res)
        return null;
    return $res["SendUserBlockNotification"];
}

?>