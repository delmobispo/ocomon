<?php
include_once "Worktime.php";

function daysFullWorkTime(array $workTimes, ?string $index = null){
    
    //PRESEATS - 24/7
    if ($workTimes['247']){
        return 1440;
    }
    
    if ($workTimes[$index]['iniTimeHour'] == null){
        return 0;
    }

    $startTime = new DateTime("{$workTimes[$index]['iniTimeHour']}:{$workTimes[$index]['iniTimeMinute']}");
    $endTime = new DateTime("{$workTimes[$index]['endTimeHour']}:{$workTimes[$index]['endTimeMinute']}");

    $diff = $startTime->diff($endTime);

    $min = $diff->i;
    $hour = $diff->h * 60;

    return $min + $hour;
}



$worktimes = [];
$worktimes['247'] = false;
$worktimes['week']['iniTimeHour'] = "08";
$worktimes['week']['iniTimeMinute'] = "00";
$worktimes['week']['endTimeHour'] = "18";
$worktimes['week']['endTimeMinute'] = "00";
$worktimes['week']['dayFullWorkTime'] = daysFullWorkTime($worktimes, 'week');
$worktimes['week']['dayFullWorkTimeInSecs'] = daysFullWorkTime($worktimes, 'week') * 60;

$worktimes['sat']['iniTimeHour'] = "08";
$worktimes['sat']['iniTimeMinute'] = "00";
$worktimes['sat']['endTimeHour'] = "13";
$worktimes['sat']['endTimeMinute'] = "00";
$worktimes['sat']['dayFullWorkTime'] = daysFullWorkTime($worktimes, 'sat');
$worktimes['sat']['dayFullWorkTimeInSecs'] = daysFullWorkTime($worktimes, 'sat') * 60;

$worktimes['sun']['iniTimeHour'] = "00";
$worktimes['sun']['iniTimeMinute'] = "00";
$worktimes['sun']['endTimeHour'] = "00";
$worktimes['sun']['endTimeMinute'] = "00";
$worktimes['sun']['dayFullWorkTime'] = daysFullWorkTime($worktimes, 'sun');
$worktimes['sun']['dayFullWorkTimeInSecs'] = daysFullWorkTime($worktimes, 'sun') * 60;

$worktimes['off']['iniTimeHour'] = "00";
$worktimes['off']['iniTimeMinute'] = "00";
$worktimes['off']['endTimeHour'] = "00";
$worktimes['off']['endTimeMinute'] = "00";
$worktimes['off']['dayFullWorkTime'] = daysFullWorkTime($worktimes, 'off');
$worktimes['off']['dayFullWorkTimeInSecs'] = daysFullWorkTime($worktimes, 'off') * 60;

$worktimes['workHolidays'] = false;

print "<pre>";
print_r($worktimes);
print "</pre>";

echo "<h1>" . daysFullWorkTime($worktimes, 'sun') * 60 . "</h1>";

$hollidays = [];
$hollidays[] = "2020-04-04";
$hollidays[] = "2020-04-14";
$hollidays[] = "2020-04-20";


$date1 = "2020-04-04 08:00:00";
$date2 = "2020-04-05 18:00:05";
$date3 = "2020-04-08 08:00:00";
$date4 = "2020-04-09 18:00:05";
$date5 = "2020-04-12 08:00:00";
$date6 = "2020-04-17 18:00:05";


//$myPeriod = new WorkTime($date1, $date2, $worktimes, $hollidays);
$myPeriod = new Worktime($worktimes, $hollidays);

$myPeriod->startTimer($date1);
$myPeriod->stopTimer($date2);

$myPeriod->startTimer($date3);
$myPeriod->stopTimer($date4);

$myPeriod->startTimer($date5);
$myPeriod->stopTimer(date("Y-m-d H:i:s"));


echo $myPeriod->getTime();

var_dump([
    $myPeriod
    //$myPeriod->countWeekendDays(),
    //$myPeriod->hasFullDays()
]);



/* $myPeriod->startTimer($date3);
$myPeriod->stopTimer($date4);

var_dump([
    $myPeriod
]); */
