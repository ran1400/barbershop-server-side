<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 

$startHour = $_POST["startHour"]; //hhmm
$endHour = $_POST["endHour"]; //hhmm
$timeBetweenQueues = $_POST["timeBetweenQueues"]; //mm
$datesList = $_POST["datesList"]; //list of yyyymmdd

$queuesToAdd = [];  
$len = strlen($datesList);

for ($i = 0; $i < $len; $i += 8) 
{
    $crntDate = substr($datesList, $i, 8);
    $startTime = $crntDate * 10000 + $startHour;//yyyymmddhhmm
    $endTime = $crntDate * 10000 + $endHour; //yyyymmddhhmm
    $sameDayQueues = addSameDayQueues($timeBetweenQueues, $startTime, $endTime);
    foreach ($sameDayQueues as $queue)
        $queuesToAdd[] = $queue;
}

$placeholders = implode(',', array_fill(0, count($queuesToAdd), '(?)'));
$query = "INSERT IGNORE INTO EmptyQueue VALUES " . $placeholders;
$conn->begin_transaction();
$queuesInserted = runExecQuery($conn,$query,$queuesToAdd);
if ($queuesInserted === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
$query = "SELECT ReservedQueue.Time FROM ReservedQueue JOIN EmptyQueue ON ReservedQueue.Time = EmptyQueue.Time";
$res = mysqli_query($conn,$query);
if ($res === false)
    die(json_encode(["error" => "cmd failed : " . $query]));
$res = mysqli_fetch_assoc($res);
if ($res !== null)
    die(json_encode(["error" => "reservedQueueExistInThisDates"]));
$conn->commit();
echo json_encode(["queuesInserted" => $queuesInserted]);


function addSameDayQueues($timeBetweenQueues,$startTime,$endTime)
{
    $queuesToAdd = [];
    $crntTime = $startTime;
    $tmp = addSpaceToDate($crntTime,$timeBetweenQueues);
    while( $tmp <= $endTime ) // start time and end time are in the same day
    {
        $queuesToAdd[] = $crntTime ."00"; //00 for the seconds 
        $crntTime = $tmp;
        $tmp = addSpaceToDate($crntTime,$timeBetweenQueues) ; 
    }
    return $queuesToAdd;
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