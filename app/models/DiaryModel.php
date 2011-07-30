<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Diary model
 *
 * @author     eWide
 * @package    eWide Client
 */

class DiaryModel
{
    public static function getNextMonth($month, $year)
    {
        if ($month == 12) {
            $month = 1;
            $year = $year + 1;
        } else {
            $month = $month + 1;
        }
        return $array = (array('month' => $month, 'year' => $year));
    }
        
    public static function getPrevMonth($month, $year)
    {
        if ($month == 1) {
            $month = 12;
            $year = $year - 1;
        } else {
            $month = $month - 1;
        }
        return $array = (array('month' => $month, 'year' => $year));
    }
    
    public static function createMonth($month, $year)
    {
        $daysBefore = 0;
        $daysAfter = 0;
        $firstDay = date('N', mktime(0, 0, 0, $month, 1, $year));
        $lastDay = date('N', mktime(0, 0, 0, $month, date('t', mktime(0, 0, 0, $month, 1, $year)), $year));
         if ($firstDay != 1) {
            $daysBefore = $firstDay - 1;
         }
        if ($lastDay != 7) {
            $daysAfter = 7 - $lastDay;
        }
        $numDays = date("t", mktime(0, 0, 0, $month, 1, $year)) + $daysBefore + $daysAfter;
        
        /* Calendar generator begin */
        $draw = array();
        if ($month == 1) {
            $month = 12;
            $year--;
        } else {
            $month--;
        }
        for ($i = $daysBefore - 1; $i >= 0; $i--) {
            $draw[] = array('day' => date("t", mktime(0, 0, 0, $month, 1, $year)) - $i, 'month' => $month, 'year' => $year, 'actual' => false, 'weekend' => false, 'new_row' => '', 'visits' => '');
        }
        if ($month == 12) {
            $month = 1;
            $year++;
        } else {
            $month++;
        }
        for ($i = 1; $i != date("t", mktime(0, 0, 0, $month, 1, $year)) + 1; $i++) {
            $draw[] = array('day' => $i, 'month' => $month, 'year' => $year, 'actual' => true, 'weekend' => false, 'new_row' => '', 'visits' => '');
        }
        if ($month == 12) {
            $month = 1;
            $year++;
        } else {
            $month++;
        }
        for ($i = 1; $i != $daysAfter + 1; $i++) {
            $draw[] = array('day' => $i, 'month' => $month, 'year' => $year, 'actual' => false, 'weekend' => false, 'new_row' => '', 'visits' => '');
        }
        /* Calendar generator end */
        foreach ($draw as $key => $value) {
            if (date('N', mktime(0, 0, 0, $value['month'], $value['day'], $value['year'])) == 6) { 
                $draw[$key]['weekend'] = true;
            }
            if (date('N', mktime(0, 0, 0, $value['month'], $value['day'], $value['year'])) == 7) {
                $draw[$key]['weekend'] = true;
            }
            if (date('N', mktime(0, 0, 0, $value['month'], $value['day'], $value['year'])) == 1) {
                $draw[$key]['new_row'] = self::getWeek($value['day'], $value['month'], $value['year']);
            }
        }
        return $draw;
    }
    public static function getWeek($day, $month, $year)
    {
            $week = date('W', mktime(0, 0, 0, $month, $day, $year));
        return $week;
    }
    
    public static function getVisitsToday($day, $visits)
    {
        $visitsToday = 0;
        foreach ($visits as $key => $value) {
            if ($value['date'] == $day && $value['done'] != 1) {
                $visitsToday++;
            }
        }
        return $visitsToday;
    }
}

