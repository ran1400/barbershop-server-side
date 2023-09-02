<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
else
{
    $startHour = $_POST["startHour"]; //hhmm
    $endHour = $_POST["endHour"]; //hhmm
    $timeBetweenQueues = $_POST["timeBetweenQueues"]; //mm
    $datesList = $_POST["datesList"]; //list of yyyymmdd
    $values = ""; //string of all the queue to add 
    while (strlen($datesList) > 0 )
    {
        $crntDate = substr($datesList,0,8);
        $startTime = $crntDate * 10000 + $startHour; //yyyymmddhhmm
        $endTime = $crntDate * 10000 + $endHour; //yyyymmddhhmm
        $values = $values . createSameDayValues($timeBetweenQueues,$startTime,$endTime);
        $datesList = substr($datesList, 8);
    }
    $values = substr($values, 0, -1); //remove the last char ","
    $cmd = "INSERT INTO EmptyQueue VALUES $values";
    $conn->begin_transaction();
    $query = mysqli_query($conn,$cmd);
    if ($query)
    {
        $rowInserted = $conn -> affected_rows;
        $cmd = "SELECT ReservedQueue.Time FROM ReservedQueue JOIN EmptyQueue 
                 ON ReservedQueue.Time = EmptyQueue.Time";
        $query = mysqli_query($conn,$cmd);
        $res = mysqli_fetch_assoc($query);
        if ($res['Time'] )
            die("reservedQueueExistInThisDates");
         $conn->commit();
         echo $rowInserted;
    }
    else
      echo("emptyQueueExistInThisDates");
}

function createSameDayValues($timeBetweenQueues, $startTime,$endTime)//firstTime and secondTime are the same
{
    $res = "";
    $crntTime = $startTime;
    while( (addSpaceToDate($crntTime,$timeBetweenQueues)) <= $endTime ) // start time and end time are in the same day
    {
        $res = $res . "(" . strval($crntTime)."00),"; //00 for the seconds 
        $crntTime = addSpaceToDate($crntTime,$timeBetweenQueues) ; 
    }
    return $res;
}


function addSpaceToDate($timeNum,$space)
{
    $date =  ((int)  ($timeNum / 10000) );
    $time = $timeNum % 10000; //withiut the date
    $min = ((int) ($time % 100)) ;
    $min += $space;
    $hours = (int) ($time / 100);
    while ($min >=60)
    {
            $hours += 1;
            $min = $min - 60;
    }
     return $date * 10000 + $hours * 100 + $min;
}

?>