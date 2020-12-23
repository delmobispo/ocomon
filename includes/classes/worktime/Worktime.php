<?php
/*                        Copyright 2020 Flávio Ribeiro

This file is part of OCOMON.

OCOMON is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

OCOMON is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// namespace 

/**
 * Class WorkTime
 * @description: Based on a given array of weekly workload and a given array of holidays,
 * this class implements a set of methods to calculate the valid worktime inside an interval of time.
 * @author Flávio Ribeiro
 * @package ocomonphp
 */
class WorkTime
{

    private $fullWorkTime;

    private $date1;
    
    private $date2;

    private $checkpoints;

    private $verbose;

    private $seconds;

    private $workTimes;

    private $holidays;

    private $calcWorkTime;

    private $status;

    private $error;


    /**
     * __construct
     *
     * @param array $workTimes
     * @param array $holidays
     * 
     * @return void
     */
    public function __construct(array $workTimes, array $holidays)
    {
        $this->status = "stopped";
        // if (!count($workTimes)) {
        //     $this->error[] = "The info about worktimes are empty!";
        //     return;
        // }
        $this->workTimes = $workTimes;
        $this->holidays = $holidays;
        $this->fullWorkTime = 0;

        return;
    }


    /**
     * dayIndex - Retorna o índice que será utilizado para a leitura e normalização dos horários
     * no array de referência. O índice será 'week' para os dias da semana, 'sat' para sábados e
     * 'sun' para domingos
     * 
     * @param string $date
     * 
     * @return string
     */
    private function dayIndex(string $date): string
    {
        $day = date("l", strtotime($date));
        $index = "week";
    
        if ($day == "Saturday") {
            $index = "sat";
        }
        if ($day == "Sunday") {
            $index = "sun";
        }
    
        return $index;
    }

   /**
    * isHoliday - Checks if a given date is a holiday
    * based on a given array of holidays
    * @param string $date
    * 
    * @return bool
    */
    private function isHoliday(string $date): bool
    {
        // Se nao trabalha em feriados então preciso identifica-los
        //if ($this->workTimes['workHolidays'] == false){
            //Buscar no banco para saber se é feriado
            return in_array(date("Y-m-d", strtotime($date)), $this->holidays);
        // }
        // return false;
    }
   

    /**
     * secToTime
     *
     * @param integer $secs
     * 
     * @return array
     */
    private function secToTime(int $secs): array
    {
        $time = array("seconds" => 0, "minutes" => 0, "hours" => 0, "verbose" => "");
        $time['seconds'] = $secs % 60;
        $secs = ($secs - $time['seconds']) / 60;
        $time['minutes'] = $secs % 60;
        $time['hours'] = ($secs - $time['minutes']) / 60;
        
        $time['verbose'] = $time['hours'] . "h " . $time['minutes'] . "m " . $time['seconds'] . "s";

        return $time;
    }

    /**
     * hasFullDays - Checks and counts the fulldays inside a time interval
     *
     * @return array
     */
    public function hasFullDays(): array
    {
        
        $objDate1 = new DateTime($this->date1);
        $objDate2 = new DateTime($this->date2);
        
        /** Avanço um dia para poder comparar */
        $firstDayFull = date_add($objDate1, new DateInterval('P1D'));
        $firstDayFull = date_format($firstDayFull, "Y-m-d");
        $firstDayFull = new DateTime($firstDayFull);

        /** Volto um dia para poder comparar */
        $lastDayFull = date_sub($objDate2, new DateInterval('P1D'));
        $lastDayFull = date_format($lastDayFull, "Y-m-d");
        $lastDayFull = new DateTime($lastDayFull);
        
        $diff = $firstDayFull->diff($lastDayFull);

        $debug['firstDayFull'] = $firstDayFull;
        $debug['lastDayFull'] = $lastDayFull;
        $debug['fullDaysBetween'] = ($diff->invert == 1 ? false : true);
        $debug['countFullDaysBetween'] = ($diff->invert != 1 ? $diff->days + 1 : 0);

        $debug['fullDaysSundays'] = $this->countWeekendDays()['countSundays'];
        $debug['fullDaysSaturdays'] = $this->countWeekendDays()['countSaturdays'];
        $debug['fullDaysWeekdays'] = $this->countWeekendDays()['countWeekdays'];
        $debug['fullDaysHolidays'] = $this->countWeekendDays()['countHolidays'];

        return $debug;
    }


    /**
     * treatPartDay - Normalize the edges of the first and the last daytime of a time period
     *
     * @param object $date
     * @param string $point
     * 
     * @return array
     */
    private function treatPartDay(object $date, string $point = "INI"): array
    {
        $date = (array) $date;
        $formatDay = explode(".", $date['date']);
        $hour = date("H", strtotime($formatDay[0]));
        $min = date("i", strtotime($formatDay[0]));
        $sec = date("s", strtotime($formatDay[0]));

        $index = $this->dayIndex(date("Y-m-d H:i:s", strtotime($formatDay[0])));

        $isSunday = ($index == "sun" ? true : false);
        $isSaturday = ($index == "sat" ? true : false);

        if ($this->isHoliday(date("Y-m-d", strtotime($formatDay[0])))){
            $index = "off";
        }

        if ($point == "INI") {
            $roudDate = array("hour_ini" => $hour, "minute_ini" => $min, "second_ini" => $sec, "hour_end" => ($this->workTimes[$index]['endTimeHour'] ?? 0), "minute_end" => ($this->workTimes[$index]['endTimeMinute'] ?? 0), "second_end" => 0, "isSunday" => $isSunday, "isSaturday" => $isSaturday);

            //Comparando com o limite inferior para o horário de início válido
            if ($hour < $this->workTimes[$index]['iniTimeHour']){
                $roudDate['hour_ini'] = ($this->workTimes[$index]['iniTimeHour'] ?? 0);
                $roudDate['minute_ini'] = ($this->workTimes[$index]['iniTimeMinute'] ?? 0);
                $roudDate['second_ini'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['iniTimeHour'] && $min < $this->workTimes[$index]['iniTimeMinute']) {
                $roudDate['minute_ini'] = $this->workTimes[$index]['iniTimeMinute'];
                $roudDate['second_ini'] = 0;
            } 
            //Comparando com o limite superior para o horário de início válido
            elseif ($hour > $this->workTimes[$index]['endTimeHour']) {
                $roudDate['hour_ini'] = ($this->workTimes[$index]['endTimeHour'] ?? 0);
                $roudDate['minute_ini'] = $this->workTimes[$index]['endTimeMinute'];
                $roudDate['second_ini'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['endTimeHour'] && $min > $this->workTimes[$index]['endTimeMinute']){
                $roudDate['minute_ini'] = $this->workTimes[$index]['endTimeMinute'];
                $roudDate['second_ini'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['endTimeHour'] && $min == $this->workTimes[$index]['endTimeMinute']){
                $roudDate['second_ini'] = 0;
            }

            $startPoint = new DateTime("{$roudDate['hour_ini']}:{$roudDate['minute_ini']}:{$roudDate['second_ini']}");
            $endPoint = new DateTime("{$roudDate['hour_end']}:{$roudDate['minute_end']}:{$roudDate['second_end']}");

            $roudDate['startPoint'] = $startPoint;
            $roudDate['endPoint'] = $endPoint;

            $diff = $startPoint->diff($endPoint);
            $roudDate['minutes_start_time'] = ($diff->h * 60) + $diff->i; 
            $roudDate['seconds_start_time'] = $roudDate['minutes_start_time'] * 60  + $diff->s;
        
        } else {
            $roudDate = array("hour_ini" => ($this->workTimes[$index]['iniTimeHour'] ?? 0), "minute_ini" => $this->workTimes[$index]['iniTimeMinute'], "second_ini" => 0, "hour_end" => $hour, "minute_end" => $min, "second_end" => $sec, "isSunday" => $isSunday, "isSaturday" => $isSaturday);

            //Comparando com o limite inferior para o horário de FIM válido
            if ($hour < $this->workTimes[$index]['iniTimeHour']){
                $roudDate['hour_end'] = ($this->workTimes[$index]['iniTimeHour'] ?? 0);
                $roudDate['minute_end'] = $this->workTimes[$index]['iniTimeMinute'];
                $roudDate['second_end'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['iniTimeHour'] && $min < $this->workTimes[$index]['iniTimeMinute']) {
                $roudDate['minute_end'] = $this->workTimes[$index]['iniTimeMinute'];
                $roudDate['second_end'] = 0;
            } 
            //Comparando com o limite superior para o horário de FIM válido
            elseif ($hour > $this->workTimes[$index]['endTimeHour']) {
                $roudDate['hour_end'] = ($this->workTimes[$index]['endTimeHour'] ?? 0);
                $roudDate['minute_end'] = $this->workTimes[$index]['endTimeMinute'];
                $roudDate['second_end'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['endTimeHour'] && $min > $this->workTimes[$index]['endTimeMinute']) {
                $roudDate['minute_end'] = $this->workTimes[$index]['endTimeMinute'];
                $roudDate['second_end'] = 0;
            }
            elseif ($hour == $this->workTimes[$index]['endTimeHour'] && $min == $this->workTimes[$index]['endTimeMinute']) {
                $roudDate['second_end'] = 0;
            }
            
            $startPoint = new DateTime("{$roudDate['hour_end']}:{$roudDate['minute_end']}:{$roudDate['second_end']}");
            $endPoint = new DateTime("{$roudDate['hour_ini']}:{$roudDate['minute_ini']}:{$roudDate['second_ini']}");

            $roudDate['startPoint'] = $endPoint;
            $roudDate['endPoint'] = $startPoint;

            $diff = $endPoint->diff($startPoint);
            $roudDate['minutes_end_time'] = ($diff->h * 60) + $diff->i; 
            $roudDate['seconds_end_time'] = $roudDate['minutes_end_time'] * 60 + $diff->s;
            
        }
        return $roudDate;
    }

    /**
     * treatSameDay - Normalize the edges of a time period in the same day
     *
     * @param object $date1
     * @param object $date2
     * 
     * @return array
     */
    private function treatSameDay(object $date1, object $date2): array
    {
        $date1 = (array) $date1;
        $formatDay1 = explode(".", $date1['date']);
        $hour1 = date("H", strtotime($formatDay1[0]));
        $min1 = date("i", strtotime($formatDay1[0]));
        $sec1 = date("s", strtotime($formatDay1[0]));

        $date2 = (array) $date2;
        $formatDay2 = explode(".", $date2['date']);
        $hour2 = date("H", strtotime($formatDay2[0]));
        $min2 = date("i", strtotime($formatDay2[0]));
        $sec2 = date("s", strtotime($formatDay2[0]));

        $index = $this->dayIndex(date("Y-m-d H:i:s", strtotime($formatDay1[0])));

        if ($this->isHoliday(date("Y-m-d", strtotime($formatDay1[0])))){
            $index = "off";
        }

        $roundDate1 = array("hour_ini" => $hour1, "minute_ini" => $min1, "second_ini" => $sec1, "hour_end" => ($this->workTimes[$index]['endTimeHour'] ?? 0), "minute_end" => ($this->workTimes[$index]['endTimeMinute'] ?? 0), "second_end" => 0);

        //Comparando com o limite inferior para o horário de início válido
        if ($hour1 < $this->workTimes[$index]['iniTimeHour']){
            $roundDate1['hour_ini'] = $this->workTimes[$index]['iniTimeHour'];
            $roundDate1['minute_ini'] = $this->workTimes[$index]['iniTimeMinute'];
            $roundDate1['second_ini'] = 0;
        }
        elseif ($hour1 == $this->workTimes[$index]['iniTimeHour'] && $min1 < $this->workTimes[$index]['iniTimeMinute']) {
            $roundDate1['minute_ini'] = $this->workTimes[$index]['iniTimeMinute'];
            $roundDate1['second_ini'] = 0;
        } 
        //Comparando com o limite superior para o horário de início válido
        elseif ($hour1 > $this->workTimes[$index]['endTimeHour']) {
            $roundDate1['hour_ini'] = $this->workTimes[$index]['endTimeHour'];
            $roundDate1['minute_ini'] = $this->workTimes[$index]['endTimeMinute'];
            $roundDate1['second_ini'] = 0;
        }
        elseif ($hour1 == $this->workTimes[$index]['endTimeHour'] && $min1 > $this->workTimes[$index]['endTimeMinute']){
            $roundDate1['minute_ini'] = $this->workTimes[$index]['endTimeMinute'];
            $roundDate1['second_ini'] = 0;
        }
        elseif ($hour1 == $this->workTimes[$index]['endTimeHour'] && $min1 == $this->workTimes[$index]['endTimeMinute']){
            $roundDate1['second_ini'] = 0;
        }
        $startPoint = new DateTime("{$roundDate1['hour_ini']}:{$roundDate1['minute_ini']}:{$roundDate1['second_ini']}");
        
        $roundDate2 = array("hour_ini" => ($this->workTimes[$index]['iniTimeHour'] ?? 0), "minute_ini" => ($this->workTimes[$index]['iniTimeMinute'] ?? 0), "second_ini" => 0, "hour_end" => $hour2, "minute_end" => $min2, "second_end" => $sec2);

        //Comparando com o limite inferior para o horário de FIM válido
        if ($hour2 < $this->workTimes[$index]['iniTimeHour']){
            $roundDate2['hour_end'] = $this->workTimes[$index]['iniTimeHour'];
            $roundDate2['minute_end'] = $this->workTimes[$index]['iniTimeMinute'];
            $roundDate2['second_end'] = 0;
        }
        elseif ($hour2 == $this->workTimes[$index]['iniTimeHour'] && $min2 < $this->workTimes[$index]['iniTimeMinute']) {
            $roundDate2['minute_end'] = $this->workTimes[$index]['iniTimeMinute'];
            $roundDate2['second_end'] = 0;
        } 
        //Comparando com o limite superior para o horário de FIM válido
        elseif ($hour2 > $this->workTimes[$index]['endTimeHour']) {
            $roundDate2['hour_end'] = $this->workTimes[$index]['endTimeHour'];
            $roundDate2['minute_end'] = $this->workTimes[$index]['endTimeMinute'];
            $roundDate2['second_end'] = 0;
        }
        elseif ($hour2 == $this->workTimes[$index]['endTimeHour'] && $min2 > $this->workTimes[$index]['endTimeMinute']){
            $roundDate2['minute_end'] = $this->workTimes[$index]['endTimeMinute'];
            $roundDate2['second_end'] = 0;
        }
        elseif ($hour2 == $this->workTimes[$index]['endTimeHour'] && $min2 == $this->workTimes[$index]['endTimeMinute']){
            $roundDate2['second_end'] = 0;
        }
        $endPoint = new DateTime("{$roundDate2['hour_end']}:{$roundDate2['minute_end']}:{$roundDate2['second_end']}");
        
        $diff = $startPoint->diff($endPoint);
        $debug['minutesSameDay'] = ($diff->h * 60) + $diff->i;
        $debug['secondsSameDay'] = $debug['minutesSameDay'] * 60 + $diff->s;

        return $debug;
    }


   /**
    * isSunday
    *
    * @param string $date
    * 
    * @return bool
    */
    function isSunday(string $date): bool
    {
        if (date("l", strtotime($date)) == "Sunday"){
            return true;
        }
        return false;
    }

   /**
    * isSaturday
    *
    * @param string $date
    * 
    * @return bool
    */
    function isSaturday(string $date): bool
    {
        if (date("l", strtotime($date)) == "Saturday"){
            return true;
        }
        return false;
    }

    /**
     * countWeekendDays - Counts each type of days inside a period of time
     *
     * @return array
     */
    public function countWeekendDays(): array
    {
        $beginNew = new DateTime($this->date1);
        $beginNew = date_add($beginNew, new DateInterval('P1D'));

        $endNew = new DateTime($this->date2);
        $endNew = date_sub($endNew, new DateInterval('P1D'));
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($beginNew, $interval ,$endNew);

        $countSundays = 0;
        $countSaturdays = 0;
        $countWeekdays = 0;

        $countInnerHolidays = 0;

        foreach($daterange as $date){
            

            if ($this->isHoliday(strtotime($date->format("Y-m-d")))){
                $countInnerHolidays ++;
            }
            
            $day = date("l", strtotime($date->format("Y-m-d")));
            /* $dayTime = date("d-m-Y", strtotime($date->format("Y-m-d")));
            $debug[$dayTime] = $day; */
            
            if ($day == "Saturday") {
                $countSaturdays ++;
            } elseif ($day == "Sunday") {
                $countSundays ++;
            } else {
                $countWeekdays ++;
            }
        } 
        $debug['countSaturdays'] = $countSaturdays;
        $debug['countSundays'] = $countSundays;
        $debug['countWeekdays'] = $countWeekdays;
        $debug['countHolidays'] = $countInnerHolidays;

        return $debug;
    }

    /**
     * countInnerHolidays - Counts the number of holidays inside a period of time
     *
     * @return array
     */
    private function countInnerHolidays(): array
    {
        $beginNew = new DateTime($this->date1);
        $endNew = new DateTime($this->date2);
        
        $beginNew = date_add($beginNew, new DateInterval('P1D'));
        $endNew = date_sub($endNew, new DateInterval('P1D'));
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($beginNew, $interval ,$endNew);

        $debug['week_holidays'] = 0;
        $debug['sat_holidays'] = 0;
        $debug['sun_holidays'] = 0;
        $debug['total_holidays'] = 0;

        foreach($daterange as $date){
            
            $day = date("Y-m-d", strtotime($date->format("Y-m-d")));
            $dayOfWeek = date("l", strtotime($day));
            
            // $debug[] = $day;
            
            // if ($this->isHoliday($day, $this->workTimes, $this->holidays)) {
            if ($this->isHoliday($day)) {
                
                $debug['total_holidays']++;

                if ($dayOfWeek == "Saturday"){
                    $debug['sat_holidays']++;
                } 
                elseif ($dayOfWeek == "Sunday"){
                    $debug['sun_holidays']++;
                } 
                else {
                    $debug['week_holidays']++;
                }
            }
        }

        return $debug;
    }


    /**
     * calcWorkTime - Does the math
     *
     * @return void
     */
    public function calcWorkTime()
    {
        //Testo para saber se os dias das datas informadas são os mesmos. Ao testar primeiro sobre a existência de fullDays posso saber se as datas informadas têm o mesmo dia.
        $isSameDay = (date("d", strtotime($this->date1)) == date("d", strtotime($this->date2)) ? true : false);
        
        $objDate1 = new DateTime($this->date1);
        $objDate2 = new DateTime($this->date2);

        $arrayFullDays = $this->hasFullDays();

        if ($arrayFullDays['fullDaysBetween']) {
            
            $arrayTreatPartDay1 = $this->treatPartDay($objDate1, "INI");
            
            $workTimeFirstDay = $arrayTreatPartDay1['seconds_start_time'];
            $firstDayIsSunday = $arrayTreatPartDay1['isSunday'];
            $firstDayIsSaturday = $arrayTreatPartDay1['isSaturday'];
            
            $arrayTreatPartDay2 = $this->treatPartDay($objDate2, "END");

            $workTimeLastDay = $arrayTreatPartDay2['seconds_end_time'];
            $lastDayIsSunday = $arrayTreatPartDay2['isSunday'];
            $lastDayIsSaturday = $arrayTreatPartDay2['isSaturday'];

            $arrayOffDays = $this->countInnerHolidays();

            $workTimeSingleWeekDay = $this->workTimes['week']['dayFullWorkTimeInSecs'];
            $workTimeSingleSatDay = $this->workTimes['sat']['dayFullWorkTimeInSecs'];
            $workTimeSingleSunDay = $this->workTimes['sun']['dayFullWorkTimeInSecs'];

            $workTimeSingleOffDay = $this->workTimes['off']['dayFullWorkTimeInSecs'];

            $debug['workTimeFirstDay'] = $workTimeFirstDay;
            $debug['workTimeLastDay'] = $workTimeLastDay;

            $debug['workTimeSigleWeekDay'] = $workTimeSingleWeekDay;
            $debug['workTimeSigleSatDay'] = $workTimeSingleSatDay;
            $debug['workTimeSigleSunDay'] = $workTimeSingleSunDay;
            $debug['workTimeSigleOffDay'] = $workTimeSingleOffDay;
            // $debug['workTimeSigleOffDay'] = ;

            $saturdays = $arrayFullDays['fullDaysSaturdays'];
            $sundays = $arrayFullDays['fullDaysSundays'];

            $weekDays = $arrayFullDays['fullDaysWeekdays'];

            $offSaturdays = $arrayOffDays['sat_holidays'];
            $offSundays = $arrayOffDays['sun_holidays'];
            $offWeekDays = $arrayOffDays['week_holidays'];
            $totalOffDays = $arrayOffDays['total_holidays'];

            // $workTimeAllFullDays = (($weekDays - $offWeekDays) * $workTimeSingleWeekDay) + (($saturdays - $offSaturdays) * $workTimeSingleSatDay) + (($sundays - $offSundays) * $workTimeSingleSunDay);
            /** Total of time in the fulldays between the period */
            $workTimeAllFullDays = (($weekDays - $offWeekDays) * $workTimeSingleWeekDay) + (($saturdays - $offSaturdays) * $workTimeSingleSatDay) + (($sundays - $offSundays) * $workTimeSingleSunDay) + ($totalOffDays * $workTimeSingleOffDay);
            
            $this->weekDays = $weekDays;
            $this->workTimeSingleWeekDay = $workTimeSingleWeekDay;
            $this->saturdays = $saturdays;
            $this->workTimeSingleSatDay = $workTimeSingleSatDay;
            $this->sundays = $sundays;
            $this->workTimeSigleSunDay = $workTimeSingleSunDay;

            $debug['workTimeAllFullDays'] = $workTimeAllFullDays;

            $debug['firstDayIsSunday'] = $firstDayIsSunday;
            $debug['firstDayIsSaturday'] = $firstDayIsSaturday;
            $debug['lastDayIsSunday'] = $lastDayIsSunday;
            $debug['lastDayIsSaturday'] = $lastDayIsSaturday;


            $debug['offWeekDays']  = $offWeekDays;
            $debug['offSaturdays'] = $offSaturdays;
            $debug['offSundays'] = $offSundays;

            // $debug['arrayOffDays'] = $arrayOffDays;
            
            $debug['fullWorkTime'] = $workTimeFirstDay + $workTimeAllFullDays + $workTimeLastDay;

            $this->fullWorkTime += $debug['fullWorkTime'];
            
            $debug['verbFullWorkTime'] = $this->secToTime($debug['fullWorkTime']);

            $this->seconds = $this->fullWorkTime;
            $this->verbose = $this->secToTime($this->fullWorkTime)['verbose'];

            $debug['condition'] = "fullDays > 0";

            return $debug;
        }
        elseif ($isSameDay) {

            $debug['isSunday'] = $this->isSunday($this->date1);
            $debug['isSaturday'] = $this->isSaturday($this->date1);

            $workTimeSameDay = $this->treatSameDay($objDate1, $objDate2)['secondsSameDay'];

            $debug['fullWorkTime'] = $workTimeSameDay;
            
            $this->fullWorkTime += $debug['fullWorkTime'];

            $debug['verbFullWorkTime'] = $this->secToTime($debug['fullWorkTime']);

            $this->seconds = $this->fullWorkTime;
            $this->verbose = $this->secToTime($this->fullWorkTime)['verbose'];

            $debug['condition'] = "same day";

            return $debug;
        } else {
            
            $arrayTreatPartDay1 = $this->treatPartDay($objDate1, "INI");
            
            $workTimeFirstDay = $arrayTreatPartDay1['seconds_start_time'];
            $firstDayIsSunday = $arrayTreatPartDay1['isSunday'];
            $firstDayIsSaturday = $arrayTreatPartDay1['isSaturday'];
            
            $arrayTreatPartDay2 = $this->treatPartDay($objDate2, "END");

            $workTimeLastDay = $arrayTreatPartDay2['seconds_end_time'];
            $lastDayIsSunday = $arrayTreatPartDay2['isSunday'];
            $lastDayIsSaturday = $arrayTreatPartDay2['isSaturday'];
            
            $debug['workTimeFirstDay'] = $workTimeFirstDay;
            $debug['workTimeLastDay'] = $workTimeLastDay;
            $debug['firstDayIsSunday'] = $firstDayIsSunday;
            $debug['firstDayIsSaturday'] = $firstDayIsSaturday;
            $debug['lastDayIsSunday'] = $lastDayIsSunday;
            $debug['lastDayIsSaturday'] = $lastDayIsSaturday;
            $debug['fullWorkTime'] = $workTimeFirstDay + $workTimeLastDay;
            
            $this->fullWorkTime += $debug['fullWorkTime'];

            $debug['verbFullWorkTime'] = $this->secToTime($debug['fullWorkTime']);
            
            $this->seconds = $this->fullWorkTime;
            $this->verbose = $this->secToTime($this->fullWorkTime)['verbose'];

            $debug['condition'] = "different days without fullDays";

            return $debug;
        }
    }

    /**
     * startTimer
     *
     * @param string $date
     * 
     * @return void
     */
    public function startTimer(string $date): void
    {
        if ($this->status == "started"){
            $this->error[] = "The timer is already started. You need to stop it first in order to start it again.";
            return;
        }

        // if (count($this->error)) {
        //     print_r($this->getError());
        //     return;
        // }
        
        $this->status = "started";
        $this->date1 = $date;
        $this->checkpoints[$date] = $this->status; 

        return;
    }


    /**
     * stopTimer
     *
     * @param string $date
     * 
     * @return void
     */
    public function stopTimer(string $date): void
    {
        if ($this->status == "stopped"){
            $this->error[] = "The timer is already stopped. You need to start it first in order to stop it again.";
            return;
        }

        // if (count($this->error)) {
        //     print_r($this->getError());
        //     return;
        // }

        $this->status = "stopped";
        $this->date2 = $date;
        $this->checkpoints[$date] = $this->status;
        $this->calcWorkTime = $this->calcWorkTime();

        return;
    }


    /**
     * getTime
     *
     * @return string
     */
    public function getTime(): string
    {
        return $this->verbose;
    }

    /**
     * getSeconds
     *
     * @return string
     */
    public function getSeconds(): string
    {
        return $this->seconds;
    }

    /**
     * getError
     *
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }
}
