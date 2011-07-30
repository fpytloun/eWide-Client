<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * OverviewPresenter
 *
 * @author     eWide
 * @package    eWide Client
 */

class OverviewPresenter extends BasePresenter
{
    public function renderVisits()
    {
        $visits = ClientModel::getVisits(null, $this->params['date_from'], $this->params['date_to']);
        
        if (!empty($visits)) {
            $form = new Form();
            
            foreach ($visits as $key => $visit) {
                $visit['notes'] = str_replace("\n", '<br />', $visit['notes']);
                
                if (strtotime($visit['date']) <= time()) {
                    $visit['today'] = true;
                }
                
                $visit['date'] = date(SettingsModel::getValue('date_format', $this->user->id), strtotime($visit['date']));
                
                $form->addCheckbox('visit_'.$visit['id']);
            }
            
            $form->addSelect('action', 'Provést', array('done' => 'Označit jako proběhlé', 'delete' => 'Smazat schůzky'));
            $form->addSubmit('submit', 'Provést')->getControlPrototype()->class("normal-button");
        
            
            $this->template->visits = $visits;
            $this->template->form = $form;
            
            if ($form->isSubmitted()) {
                $values = $form->getValues();
                ClientModel::toggleVisits($values);
                if ($values['action'] == 'done') {
                    NotifyModel::addNotify(array('text' => 'Vybrané schůzky byly označeny jako proběhlé', 'class' => 'success'));
                } else {
                    NotifyModel::addNotify(array('text' => 'Vybrané schůzky byly smazány', 'class' => 'success'));
                }
                $this->redirect('Overview:visits');
            }
        }
    }
    
    public function renderOrders()
    {
        $orders = ClientModel::getOrders(null, $this->params['date_from'], $this->params['date_to']);
        
        if (!empty($orders)) {
            $totalPrice = 0;
            $totalCount = 0;
            foreach ($orders as $key => $order) {
                $order['date'] = date(SettingsModel::getValue('date_format', $this->user->id), strtotime($order['date']));
                $totalPrice = $totalPrice + $order['price'];
                $totalCount++;
                $order['price'] = PriceModel::render($order['price']);
                $order['notes'] = str_replace("\n", '<br />', $order['notes']);
            }
            
            $this->template->orders = $orders;
            $this->template->totalPrice = PriceModel::render($totalPrice);
            $this->template->totalCount = $totalCount;
        }
    }
    
    /* Form to show records in date range */
    public function createComponentShowForm()
    {
        $date_from = (empty($this->params['date_from'])) ? date('Y-m-d', mktime(0, 0, 0, date("m")-1, date("d"), date("Y"))) : $this->params['date_from'];
        $date_to = (empty($this->params['date_to'])) ? date('Y-m-d', mktime(0, 0, 0, date("m")+1, date("d"), date("Y"))) : $this->params['date_to'];
        
        $form = new AppForm();
        $form->addDatePicker('date_from', 'Od', 10, 10)->setDefaultValue($date_from);
        $form->addDatePicker('date_to', 'Do', 10, 10)->setDefaultValue($date_to);
        $form->addSubmit('submit', 'Zobrazit')->getControlPrototype()->class("normal-button inline");
        
        $form->onSubmit[] = callback($this, 'showFormSubmitted');
        
        return $form;
    }
    
    public function showFormSubmitted($form)
    {
        $values = $form->getValues();
        $this->redirect('Overview:'.$this->action, array('date_from' => $values['date_from'], 'date_to' => $values['date_to']));
    }
}
