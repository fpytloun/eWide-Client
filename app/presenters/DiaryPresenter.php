<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * DiaryPresenter
 *
 * @author     eWide
 * @package    eWide Client
 */

class DiaryPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $month = BaseModel::getMonths();
        $year = $this->params['year'];
        if ($this->params['month'] == NULL) {
            $this->params['month'] = date('n');
        }
        if ($this->params['year'] == NULL) {
            $this->params['year'] = date('Y');
        }
        $calendar = DiaryModel::createMonth($this->params['month'], $this->params['year']);
        $visitsFrom = date('Y-m-d', mktime(0, 0, 0, $calendar[0]['month'], $calendar[0]['day'], $calendar[0]['year']));
        $visitsTo = end($calendar);
        $visitsTo = date('Y-m-d', mktime(0, 0, 0, $visitsTo['month'], $visitsTo['day'], $visitsTo['year']));
        $visits = ClientModel::getVisits(NULL, $visitsFrom, $visitsTo);
        $calendar = DiaryModel::createMonth($this->params['month'], $this->params['year']);
        foreach ($calendar as $key => $value) {            
            $calendar[$key]['visits'] = DiaryModel::getVisitsToday(date('Y-m-d', mktime(0, 0, 0, $value['month'], $value['day'], $value['year'])), $visits);
        }
        $prevMonth = DiaryModel::getPrevMonth($this->params['month'], $this->params['year']);
        $nextMonth = DiaryModel::getNextMonth($this->params['month'], $this->params['year']);
        $this->template->monthName = $month[$this->params['month']].' '.$year;
        $this->template->previousMonth = $this->link('Diary:', array('month' => $prevMonth['month'], 'year' => $prevMonth['year']));
        $this->template->nextMonth = $this->link('Diary:', array('month' => $nextMonth['month'], 'year' => $nextMonth['year']));
        $this->template->calendar = $calendar;
    }
}
