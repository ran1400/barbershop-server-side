<?php

function checkGoogleLogin($idToken)
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
    curl_close($ch);
    
    if (curl_errno($ch)) 
        return false;
    else
    {
        $responseObject = json_decode($response);
        $aud = $responseObject->aud;
        if ($aud) // success login
        {
            if ($aud != "256273489041-5gomh891qc8b5m1kpsoi4tm9equ4orp4.apps.googleusercontent.com") // the login was to my app 
                return false;
            $email = $responseObject->email;
            return $email;
        }
        else
            return false;
    } 
}



?>