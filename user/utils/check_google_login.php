<?php


function checkGoogleLogin($idToken,$userMail)
{
    $data = array('id_token' => $idToken);
    $postData = http_build_query($data);

    $ch = curl_init();
    $url = 'https://oauth2.googleapis.com/tokeninfo';

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    $curlError = curl_errno($ch);
    curl_close($ch);

    if ($curlError || !$response) 
        return false;

    $responseObject = json_decode($response);

    if ( !isset($responseObject->aud) || !isset($responseObject->exp) || !isset($responseObject->iss) || !isset($responseObject->email) )
        return false;

    $validAudiences = [
        "256273489041-5gomh891qc8b5m1kpsoi4tm9equ4orp4.apps.googleusercontent.com", // my android app
        "952791177175-4k8gve54jhvps9v3rv9s761coh3kkjqg.apps.googleusercontent.com" // my web
    ];

    if (!in_array($responseObject->aud, $validAudiences)) 
        return false;

    if ( $responseObject->iss !== "https://accounts.google.com" && $responseObject->iss !== "accounts.google.com") 
        return false;

    if ($responseObject->exp < time()) 
        return false;

    if (isset($responseObject->email_verified) && $responseObject->email_verified !== 'true') 
        return false;
    if ($responseObject->email == $userMail)
        return true;
    else
        return false;
}

?>