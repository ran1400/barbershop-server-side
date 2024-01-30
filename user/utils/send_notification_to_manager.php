<?php

function sendFCM($channel,$title,$body) 
{
    $url = 'https://fcm.googleapis.com/fcm/send';
    $apiKey = "Authorization:key=censored";
    $headers = array ($apiKey,'Content-Type:application/json');
    $notifData = [
    'title' => $title ,
    'body' => $body ,
    'android_channel_id' => $channel
     ];

    $apiBody = [
    'notification' => $notifData,
    'to' => '/topics/userUpdates' 
     ];

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($apiBody));
    curl_exec($ch);
    curl_close($ch);

}

?>