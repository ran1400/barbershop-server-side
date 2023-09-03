<?php

function sendFCM($userMail,$channel,$title,$body) 
{
    $topic = explode("@",$userMail)[0];
    $url = 'https://fcm.googleapis.com/fcm/send';
    $apiKey = "Authorization:key=AAAAO6sXHJE:APA91bGIQKppTa4hxgJiOd3XGT46m7axls80Oj0XCYNqbwC1NTdk5xHSy7cCbrPAvA_--ip4Z6UdsoxPVMzIwF7p5x_orNqKQ9ILMCps4mWGg9ofmwNo6ACM7Q2XRwQ5h0IsyaiCF1gZ";
    $headers = array ($apiKey,'Content-Type:application/json');
    $notifData = [
    'title' => $title ,
    'body' => $body ,
    'android_channel_id' => $channel
     ];

    $apiBody = [
    'notification' => $notifData,
    'to' => '/topics/'.$topic 
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