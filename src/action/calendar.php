<?php
$ret = array();
function firstday($year, $month) {
    return mktime(1, 1, 1, $month, 1, $year);
}
$time = firstday($_GET['year'], $_GET['month']);
$date = date('Y-m-d-w', $time);
list($year, $month, $day, $wday) = explode('-', $date);

$year *= 1;
$month *= 1;
$day *= 1;
$wday *= 1;

$prevyear = $year;
$prevmonth = $month - 1;
if($prevmonth < 1) {
    $prevmonth += 12;
    $prevyear -= 1;
}
$nextyear = $year;
$nextmonth = $month + 1;
if($nextmonth > 12) {
    $nextmonth -= 12;
    $nextyear += 1;
}

$prevlastday = date('d', $time - 5000) * 1;
$lastday = date('d', firstday($nextyear, $nextmonth) - 5000) * 1;

$days = array();
for($i = $wday; $i > 0; $i--) {
    $days[] = array(
        'year' => $prevyear,
        'month' => $prevmonth,
        'day' => $prevlastday - $i + 1,
    );
}
for($i = 1; $i <= $lastday; $i++) {
    $days[] = array(
        'active' => true,
        'year' => $year,
        'month' => $month,
        'day' => $i
    );
}
$j = 1;
for($i = count($days); $i < 42; $i++) {
    $days[] = array(
        'year' => $nextyear,
        'month' => $nextmonth,
        'day' => $j++
    );
}
$ny = date('Y', time()) * 1;
$nm = date('m', time()) * 1;
$nd = date('d', time()) * 1;
foreach($days as &$day) {
    $y = $day['year'];
    $m = $day['month'];
    $d = $day['day'];
    if($y == $ny && $m == $nm && $d == $nd) {
        $day['istoday'] = true;
    }
    if(data_exists("calendar/$y-$m-$d")) {
        $day['mark'] = json_decode(data_read("calendar/$y-$m-$d"), true);
    }
}

$ret['days'] = $days;
$ret['year'] = $year;
$ret['month'] = $month;
$ret['prevyear'] = $prevyear;
$ret['prevmonth'] = $prevmonth;
$ret['nextyear'] = $nextyear;
$ret['nextmonth'] = $nextmonth;
die(json_encode($ret));
