<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Client presenter.
 *
 * @author     eWide
 * @package    eWide Client
 */

class ClientPresenter extends BasePresenter
{
    public function renderShow()
    {
        // If client id is not set, redirect to homepage
        if (!isset($this->params['id'])) {
            $this->redirect('Homepage:default');
        }
        
        $client = ClientModel::getClient($this->params['id']);
        
        // If client does not exist, redirect to homepage
        if (!isset($client['id'])) {
            $this->redirect('Homepage:default');
        }
        
        $person = ClientModel::getContactPerson($client['id']);
        $last_visit = ClientModel::getLastVisit($client['id']);
        $last_order = ClientModel::getLastOrder($client['id']);
        
        if (!empty($client['city'])) {
            $client['map_url'] = SettingsModel::getValue('map_url');
            $client['map_url'] .= urlencode($client['city']);
            $client['map_url'] .= empty($client['street']) ? '' : urlencode("+".$client['street']);
        }
        
        $client['group_name'] = ClientModel::getGroupName($client['group']);
        $client['last_visit'] = (empty($last_visit['date'])) ? '' : date(SettingsModel::getValue('date_format'), strtotime($last_visit['date']));
        $client['last_order'] = (empty($last_order['date'])) ? '' : date(SettingsModel::getValue('date_format'), strtotime($last_order['date']));
        $client['added'] = (empty($client['added'])) ? '' : date(SettingsModel::getValue('date_format'), strtotime($client['added']));
        $client['last_order_price'] = (empty($last_order['date'])) ? '' : PriceModel::render($last_order['price']);
        $client['contact_person'] = (empty($person['name'])) ? '---' : $person['name'];
        $client['contact_person_position'] = (empty($person['position'])) ? '---' : $person['position'];
        $client['phone'] = (empty($client['phone'])) ? $person['phone'] : $client['phone'];
        $client['email'] = (empty($client['email'])) ? $person['email'] : $client['email'];
        
        $client['notes'] = str_replace("\n", '<br />', $client['notes']);
        $client['last_visit_notes'] = str_replace("\n", '<br />', $last_visit['notes']);
        $client['last_order_notes'] = str_replace("\n", '<br />', $last_order['notes']);
            
        foreach ($client as $key => $value) {
            $client[$key] = (empty($value)) ? '---' : $value;
        }
        
        $orders = ClientModel::getOrders($client['id']);
        $visits = ClientModel::getVisits($client['id']);
        $persons = ClientModel::getPersons($client['id']);
        
        $ordersCount = 0;
        $ordersPrice = 0;
        foreach ($orders as $key => $order) {
            $orders[$key]['notes'] = str_replace("\n", '<br />', $order['notes']);
            $orders[$key]['date'] = date(SettingsModel::getValue('date_format'), strtotime($order['date']));
            $ordersCount++;
            $ordersPrice = $ordersPrice + $order['price'];
            $orders[$key]['price'] = PriceModel::render($order['price']);
        }
        
        $visitsCount = 0;
        foreach ($visits as $key => $visit) {
            $visits[$key]['notes'] = str_replace("\n", '<br />', $visit['notes']);
            $visits[$key]['type_name'] = ClientModel::getTypeName($visit['type']);
            
            if (strtotime($visit['date']) <= time()) {
                $visits[$key]['today'] = true;
            } else {
                $visits[$key]['today'] = false;
            }
            
            $visits[$key]['date'] = date(SettingsModel::getValue('date_format'), strtotime($visit['date']));
            
            $visitsCount++;
        }
        
        foreach ($persons as $key => $person) {
            $person[$key]['notes'] = str_replace("\n", '<br />', $person['notes']);
        }
        
        $this->template->client = $client;
        $this->template->orders = $orders;
        $this->template->visits = $visits;
        $this->template->persons = $persons;
        
        $this->template->ordersPrice = PriceModel::render($ordersPrice);
        $this->template->ordersCount = $ordersCount;
        $this->template->visitsCount = $visitsCount;
        
        // Tags
        $client_tags = ClientModel::getTagsArray($client['tags']);
        foreach ($client_tags as $key => $value) {
            $tags[$value] = ClientModel::getTagName($value);
        }
        
        $this->template->tags = $tags;
    }
    
    /* Form addPerson component */
    public function addPersonFormSubmitted($form)
    {
        $values = $form->getValues();
        ClientModel::addPerson($values);
        NotifyModel::addNotify(array('text' => 'Nová kontaktní osoba byla přidána', 'class' => 'success'));
        $this->redirect('Client:show', array('id' => $values['client']));
    }
    
    protected function createComponentFormAddPerson()
    {
        $contact_person = ClientModel::getContactPerson($this->params['id']);
        
        $form = new AppForm();
        $form->addGroup('Osoba');
        $form->addHidden('client')->setDefaultValue($this->params['id']);
        $form->addText('name', 'Jméno');
        $form->addText('position', 'Pozice');
        $form->addGroup('Kontakt');
        $form->addText('phone', 'Telefon');
        $form->addText('email', 'Email');
        $form->addGroup('Poznámky');
        $form->addTextArea('notes');
        $form->setCurrentGroup(NULL);
        $form->addCheckbox('main', 'Hlavní kontakt');
        $form->addSubmit('submit', 'Uložit');
        
        if (empty($contact_person)) {
            $form['main']->setDefaultValue(true);
        }
        
        $form->onSubmit[] = callback($this, 'addPersonFormSubmitted');
        
        return $form;
    }
    
    /* Form addOrder component */
    public function addOrderFormSubmitted($form)
    {
        $values = $form->getValues();
        ClientModel::addOrder($values);
        // Add visit if type is set
        if ($values['type'] != 0) {
            ClientModel::addVisit($values);
        }
        NotifyModel::addNotify(array('text' => 'Nová objednávka byla přidána', 'class' => 'success'));
        $this->redirect('Client:show', array('id' => $values['client']));
    }
    
    protected function createComponentFormAddOrder()
    {
        $visitsTypes = ClientModel::getVisitsTypes();
        $types[0] = '---';
        foreach ($visitsTypes as $key => $type) {
            $types[$type['id']] = $type['type'];
        }

        $form = new AppForm();
        $form->addHidden('client')->setDefaultValue($this->params['id']);
        $form->addHidden('done')->setDefaultValue(true); // We need this for addVisit
        $form->addDatePicker('date', 'Datum', 10, 10)->setDefaultValue(date('Y-m-d'));
        $form->addText('price', 'Cena');
        $form->addSelect('type', 'Způsob', $types); // Used for addVisit, when id = 0, we won't add visit, otherwise we will add one
        $form->addTextArea('notes', 'Poznámky');
        $form->addSubmit('submit', 'Uložit');
        
        $form->onSubmit[] = callback($this, 'addOrderFormSubmitted');
        
        return $form;
    }
    
    /* Form addVisit component */
    public function addVisitFormSubmitted($form)
    {
        $values = $form->getValues();
        ClientModel::addVisit($values);
        NotifyModel::addNotify(array('text' => 'Nová schůzka byla přidána', 'class' => 'success'));
        $this->redirect('Client:show', array('id' => $values['client']));
    }
    
    protected function createComponentFormAddVisit()
    {
        $visitsTypes = ClientModel::getVisitsTypes();
        foreach ($visitsTypes as $key => $type) {
            $types[$type['id']] = $type['type'];
        }
        
        $form = new AppForm();
        $form->addHidden('client')->setDefaultValue($this->params['id']);
        $form->addDatePicker('date', 'Datum', 10, 10)->setDefaultValue(date('Y-m-d'));
        $form->addSelect('type', 'Způsob', $types);
        $form->addTextArea('notes', 'Poznámky');
        $form->addCheckbox('done', 'Dokončeno')->setDefaultValue(true);
        $form->addSubmit('submit', 'Uložit');
        
        $form->onSubmit[] = callback($this, 'addVisitFormSubmitted');
        
        return $form;
    }
    
    /* Form editClient component */
    public function editClientFormSubmitted($form)
    {
        $values = $form->getValues();
        
        ClientModel::editClient($values);
        NotifyModel::addNotify(array('text' => 'Klient byl úspěšně upraven', 'class' => 'success'));
        $this->redirect('Client:show', array('id' => $values['id']));
    }
    
    protected function createComponentFormEditClient()
    {
        $groups = array();
        
        $clientGroups = ClientModel::getGroups();
        $tags = ClientModel::getTags();
        $client = ClientModel::getClient($this->params['id']);
        $contact_person = ClientModel::getContactPerson($this->params['id']);
        $client_tags = ClientModel::getTagsArray($client['tags']);
        
        foreach ($clientGroups as $key => $group) {
            $groups[$group['id']] = $group['name'];
        }
        
        $form = new AppForm();
        $form->addGroup('Klient');
        $form->addHidden('id', 'Id')->setDefaultValue($client['id']);
        $form->addText('name', 'Jméno')->setDefaultValue($client['name']);
        $form->addText('ic', 'IČ')->setDefaultValue($client['ic']);
        $form->addText('dic', 'DIČ')->setDefaultValue($client['dic']);
        
        $form->addGroup('Sídlo');
        $form->addText('street', 'Ulice')->setDefaultValue($client['street']);
        $form->addText('city', 'Město')->setDefaultValue($client['city']);
        $form->addText('psc', 'PSČ')->setDefaultValue($client['psc']);
        
        $form->addGroup('Kontakt');
        $form->addText('phone', 'Telefon')->setDefaultValue($client['phone']);
        $form->addText('email', 'Email')->setDefaultValue($client['email']);
        $form->addCheckbox('send_emails', 'Zasílat hromadné emaily')->setDefaultValue($client['send_emails']);        
        $form->addText('www', 'Web')->setDefaultValue($client['www']);
        
        $form->addGroup('Poznámky');
        $form->addTextArea('notes')->setDefaultValue($client['notes']);
        
        if (!empty($tags)) {
            $form->addGroup('Nálepky');
            foreach ($tags as $key => $tag) {
                if (in_array($tag['id'], $client_tags)) {
                    $default = true;
                } else {
                    $default = false;
                }
                $form->addCheckbox('tag_'.$tag['id'], $tag['name'])->setDefaultValue($default);
            }
        }
        
        $form->setCurrentGroup(NULL);
        $form->addSelect('group', 'Skupina', $groups)->setDefaultValue($client['group']);
        $form->addCheckbox('delete', 'Smazat klienta');
        $form->addSubmit('submit', 'Uložit');
        
        $form->onSubmit[] = callback($this, 'editClientFormSubmitted');
        
        return $form;
    }
    
    public function renderEditPerson()
    {
        if (!isset($this->params['id']) || !isset($this->params['actionid'])) {
            $this->redirect('Homepage:default');
        }

        $person = ClientModel::getPerson($this->params['actionid']);
        
        $form = new Form();
        $form->addGroup('Osoba');
        $form->addHidden('id')->setDefaultValue($this->params['actionid']);
        $form->addHidden('client')->setDefaultValue($this->params['id']);
        $form->addText('name', 'Jméno')->setDefaultValue($person['name']);
        $form->addText('position', 'Pozice')->setDefaultValue($person['position']);
        $form->addGroup('Kontakt');
        $form->addText('phone', 'Telefon')->setDefaultValue($person['phone']);
        $form->addText('email', 'Email')->setDefaultValue($person['email']);
        $form->addGroup('Poznámky');
        $form->addTextArea('notes')->setDefaultValue($person['notes']);
        $form->setCurrentGroup(NULL);
        $form->addCheckbox('main', 'Hlavní kontakt')->setDefaultValue($person['main']);
        $form->addCheckbox('delete', 'Smazat osobu');
        $form->addSubmit('submit', 'Uložit');
        
        $this->template->clientid = $this->params['id'];
        $this->template->form = $form;
        
        if ($form->isSubmitted()) {
            $values = $form->getValues();
            
            ClientModel::editPerson($values);
            NotifyModel::addNotify(array('text' => 'Kontaktní osoba byla úspěšně upravena', 'class' => 'success'));
            $this->redirect('Client:show', array('id' => $this->params['id']));
        }
    }
    
    public function renderEditOrder()
    {
        if (!isset($this->params['id']) || !isset($this->params['actionid'])) {
            $this->redirect('Homepage:default');
        }

        $order = ClientModel::getOrder($this->params['actionid']);
        
        $form = new Form();
        $form->addHidden('id')->setDefaultValue($this->params['actionid']);
        $form->addDatePicker('date', 'Datum', 10, 10)->setDefaultValue($order['date']);
        $form->addText('price', 'Cena')->setDefaultValue($order['price']);
        $form->addTextArea('notes', 'Poznámky')->setDefaultValue($order['notes']);
        $form->addCheckbox('delete', 'Smazat objednávku');
        $form->addSubmit('submit', 'Uložit');
        
        $this->template->clientid = $this->params['id'];
        $this->template->form = $form;
        
        if ($form->isSubmitted()) {
            $values = $form->getValues();
            
            ClientModel::editOrder($values);
            NotifyModel::addNotify(array('text' => 'Objednávka byla úspěšně upravena', 'class' => 'success'));
            $this->redirect('Client:show', array('id' => $this->params['id']));
        }
    }
    
    public function renderEditVisit()
    {
        if (!isset($this->params['id']) || !isset($this->params['actionid'])) {
            $this->redirect('Homepage:default');
        }

        $visit = ClientModel::getVisit($this->params['actionid']);
        $visitsTypes = ClientModel::getVisitsTypes();
        foreach ($visitsTypes as $key => $type) {
            $types[$type['id']] = $type['type'];
        }
        
        $form = new Form();
        $form->addHidden('id')->setDefaultValue($this->params['actionid']);
        $form->addDatePicker('date', 'Datum', 10, 10)->setDefaultValue($visit['date']);
        $form->addSelect('type', 'Způsob', $types)->setDefaultValue($visit['type']);
        $form->addTextArea('notes', 'Poznámky')->setDefaultValue($visit['notes']);
        $form->addCheckbox('done', 'Dokončeno')->setDefaultValue($visit['done']);
        $form->addCheckbox('delete', 'Smazat schůzku');
        $form->addSubmit('submit', 'Uložit');
        
        $this->template->clientid = $this->params['id'];
        $this->template->form = $form;
        
        if ($form->isSubmitted()) {
            $values = $form->getValues();
            
            ClientModel::editVisit($values);
            NotifyModel::addNotify(array('text' => 'Schůzka byla úspěšně upravena', 'class' => 'success'));
            $this->redirect('Client:show', array('id' => $this->params['id']));
        }
    }
    
    /* Form addClient component */
    public function addClientFormSubmitted($form)
    {
        $values = $form->getValues();
        $newClient = ClientModel::addClient($values);
        
        if ($newClient) {
            NotifyModel::addNotify(array('text' => 'Nový klient byl úspěšně přidán', 'class' => 'success'));
        } else {
            NotifyModel::addNotify(array('text' => 'Nového klienta se nepodařilo přidat', 'class' => 'error'));
        }
        
        if (SettingsModel::getValue('goto_added_client') == 1) {
            $this->redirect('Client:show', array('id' => $newClient));
        } else {
            $this->redirect('Homepage:default');
        }
    }
    
    protected function createComponentFormAddClient()
    {
        $groups = array();
        
        $clientGroups = ClientModel::getGroups();
        $tags = ClientModel::getTags();
        
        foreach ($clientGroups as $key => $group) {
            $groups[$group['id']] = $group['name'];
        }
        
        $form = new AppForm();
        $form->addGroup('Klient');
        $form->addText('name', 'Jméno')->setDefaultValue(SettingsModel::getValue('default_client_name'))->setEmptyValue(SettingsModel::getValue('default_client_name'));
        $form->addText('ic', 'IČ');
        $form->addText('dic', 'DIČ');
        
        $form->addGroup('Sídlo');
        $form->addText('street', 'Ulice');
        $form->addText('city', 'Město')->setDefaultValue(SettingsModel::getValue('default_client_city'));
        $form->addText('psc', 'PSČ')->setDefaultValue(SettingsModel::getValue('default_client_psc'));
        
        $form->addGroup('Kontakt');
        $form->addText('contact_person', 'Kontaktní osoba');
        $form->addText('position', 'Pozice');
        $form->addText('phone', 'Telefon');
        $form->addText('email', 'Email');
        $form->addCheckbox('send_emails', 'Zasílat hromadné emaily')->setDefaultValue(true);
        $form->addText('www', 'Web')->setDefaultValue('http://')->setEmptyValue('http://');
        
        
        $form->addGroup('Poznámky');
        $form->addTextArea('notes');
        
        if (!empty($tags)) {
            $form->addGroup('Nálepky');
            foreach ($tags as $key => $tag) {
                $form->addCheckbox('tag_'.$tag['id'], $tag['name']);
            }
        }
        
        
        $form->setCurrentGroup(NULL);
        $form->addSelect('group', 'Skupina', $groups)->setDefaultValue(SettingsModel::getValue('default_group'));
        $form->addSubmit('submit', 'Uložit');
        
        $form->onSubmit[] = callback($this, 'addClientFormSubmitted');
        
        return $form;
    }
}
