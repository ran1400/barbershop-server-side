<?php

function toSendNotification($date,$timeStamp)
{
    require 'seconds_amount_to_send_notification.php'; //get the var $secondsAmountToSendNotification
    if ($secondsAmountToSendNotification == 1) //always send notification
        return true;
    else if ($secondsAmountToSendNotification == 0) //never send notification
        return false;
    else if ($date < $timeStamp) //past queue
        return false;
    else if ($secondsAmountToSendNotification > (strtotime($date) - strtotime($timeStamp)))
        return true;
    else
        return false;
}

?>