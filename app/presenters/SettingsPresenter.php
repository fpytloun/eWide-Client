<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Settings presenter.
 *
 * @author     eWide
 * @package    eWide Client
 */

class SettingsPresenter extends BasePresenter
{
    public function renderDefault()
    {
        // Only admin can edit global settings
        $this->params['id'] = ($this->params['id'] == 'application' && $this->user->isInRole('admin')) ? 'application' : null;
        $userid = ($this->params['id'] == 'application') ? null : $this->user->getId();
        
        // Settings form
        $clientGroups = ClientModel::getGroups();
        foreach ($clientGroups as $key => $group) {
            $groups[$group['id']] = $group['name'];
        }
        
        $languages = array(
            'cs' => 'Čeština'
        );
        
        $form = new Form();
        
        if (!$this->params['id']) {
            $form->addGroup('Uživatel');
            $form->addText('login', 'Přihlašovací jméno')->setDisabled(true)->setDefaultValue($this->identity->login);
            $form->addText('email', 'Email')->setDefaultValue($this->identity->email);
            $form->addText('phone', 'Telefon')->setDefaultValue($this->identity->phone);
            $form->addPassword('password', 'Nové heslo');
        }
        
        $form->addGroup('Zobrazení klientů');
        $form->addText('clients_per_page', 'Klientů na stránku', 2)->setDefaultValue(SettingsModel::getValue('clients_per_page', $userid));
        $form->addText('show_bot_menu', 'Zobrazit spodní menu po # záznamech', 2)->setDefaultValue(SettingsModel::getValue('show_bot_menu', $userid));
        $form->addCheckbox('colored_id', 'Obarvený sloupec Id podle skupiny')->setDefaultValue(SettingsModel::getValue('colored_id', $userid));
        
        $form->addGroup('Přidání nového klienta');
        $form->addText('default_client_name', 'Předvyplněné jméno')->setDefaultValue(SettingsModel::getValue('default_client_name', $userid));
        $form->addText('default_client_city', 'Předvyplněné město')->setDefaultValue(SettingsModel::getValue('default_client_city', $userid));
        $form->addText('default_client_psc', 'Předvyplněné PSČ')->setDefaultValue(SettingsModel::getValue('default_client_psc', $userid));
        $form->addSelect('default_group', 'Předvyplněná skupina', $groups)->setDefaultValue(SettingsModel::getValue('default_group', $userid));
        $form->addCheckbox('goto_added_client', 'Přejít na nově přidaného klienta')->setDefaultValue(SettingsModel::getValue('goto_added_client', $userid));
        
        $form->addGroup('Lokalizace');
        $form->addSelect('language', 'Jazyk', $languages)->setDefaultValue(SettingsModel::getValue('language', $userid));
        $form->addText('currency', 'Měna', 5)->setDefaultValue(SettingsModel::getValue('currency', $userid));
        $form->addText('date_format', 'Formát data', 5)->setDefaultValue(SettingsModel::getValue('date_format', $userid));
        
        $form->setCurrentGroup(NULL);
        $form->addSubmit('submit', 'Uložit');
        
        $this->template->form = $form;
        $this->template->appSettings = (isset($this->params['id'])) ? 1 : 0;
        
        if ($form->isSubmitted()) {
            $values = $form->getValues();
            if (empty($values['password'])) {
                unset($values['password']);
            } else {
                $values['password'] = sha1($values['password']);
            }
            SettingsModel::saveSettings($values, $userid);
            NotifyModel::addNotify(array('text' => 'Nová nastavení byla uložena', 'class' => 'success'));
            $this->identity->phone = $values['phone'];
            $this->identity->email = $values['email'];
            $this->redirect('Settings:default');
        }
    }
    
    public function renderUsers()
    {
        if ($this->user->isInRole('admin')) {
            $users = UsersModel::getUsers();
            
            foreach ($users as $key => $user) {
                $user['created'] = date(SettingsModel::getValue('date_format', $this->user->id)." G:i", strtotime($user['created']));
                $user['phone'] = (empty($user['phone'])) ? '---' : $user['phone'];
                $user['email'] = (empty($user['email'])) ? '---' : $user['email'];
            }
            
            $form = new Form();
            $form->addText('login', 'Jméno');
            $form->addPassword('password', 'Heslo');
            $form->addSelect('role', 'Role', array('user' => 'Uživatel', 'admin' => 'Administrátor'));
            $form->addText('email', 'Email');
            $form->addText('phone', 'Telefon');
            $form->addSubmit('submit', 'Uložit');
            
            $this->template->users = $users;
            $this->template->form = $form;
            
            if ($form->isSubmitted()) {
                $values = $form->getValues();
                if (UsersModel::addUser($values)) {
                    NotifyModel::addNotify(array('text' => 'Uživatel byl přidán', 'class' => 'success'));
                } else {
                    NotifyModel::addNotify(array('text' => 'Uživatele se nepodařilo přidat', 'class' => 'error'));
                }
                $this->redirect('Settings:users');
            }
        } else {
            $this->redirect('Settings:default');
        }
    }
    
    public function renderEditUser()
    {
        $user = UsersModel::getUser($this->params['id']);
        if ($this->user->isInRole('admin') && !empty($user)) {
            $form = new Form();
            $form->addHidden('id', 'Id')->setDefaultValue($user['id']);
            $form->addText('login', 'Jméno')->setDefaultValue($user['login']);
            $form->addPassword('password', 'Heslo');
            $form->addSelect('role', 'Role', array('user' => 'Uživatel', 'admin' => 'Administrátor'))->setDefaultValue($user['role']);
            $form->addText('email', 'Email')->setDefaultValue($user['email']);
            $form->addText('phone', 'Telefon')->setDefaultValue($user['phone']);
            $form->addCheckbox('delete', 'Smazat uživatele');
            $form->addSubmit('submit', 'Uložit');
            
            $this->template->form = $form;
            
            if ($form->isSubmitted()) {
                $values = $form->getValues();
                
                if (empty($values['password'])) {
                    unset($values['password']);
                } else {
                    $values['password'] = sha1($values['password']);
                }
                
                UsersModel::editUser($values);
                NotifyModel::addNotify(array('text' => 'Uživatel byl upraven', 'class' => 'success'));
                $this->redirect('Settings:users');
            }
        } else {
            $this->redirect('Settings:default');
        }
    }

    /* Visit types form component */
    // Add
    protected function createComponentAddVisitTypeForm()
    {
        $form = new AppForm();
        $form->addText('addtype', 'Přidat typ schůzek');
        $form->addSubmit('submit', 'Přidat');
        $form->onSubmit[] = callback($this, 'addVisitTypeSubmitted');
        return $form;
    }

    public function addVisitTypeSubmitted($form)
    {
        $values = $form->getValues();
        if (empty($values['addtype'])) {
            NotifyModel::addNotify(array('text' => 'Nelze přidat typ schůzek', 'class' => 'error'));
        } else {
            ClientModel::addVisitType($values['addtype']);
            NotifyModel::addNotify(array('text' => 'Úspěšně přidán typ schůzek', 'class' => 'success'));
            $this->redirect('Settings:clients');
            
        }
    }

    // Edit
    protected function createComponentEditVisitTypeForm()
    {
        $VisitTypes = ClientModel::getVisitsTypes();
        $array = array();
        foreach ($VisitTypes as $key => $id) {
            $array[$id['id']] = $id['type'];
        }
        $form = new AppForm();
        $form->addSelect('originaltype', 'Typ schůzek', $array);
        $form->addText('newtype', 'Přejmenovat na');
        $form->addSubmit('submit', 'Uložit');
        $form->onSubmit[] = callback($this, 'editVisitTypeSubmitted');
        return $form;
        }

    public function editVisitTypeSubmitted($form)
    {
        $values = $form->getValues();
        if (empty($values['newtype'])) {
            NotifyModel::addNotify(array('text' => 'Chyba při změně typu schůzek', 'class' => 'error'));
        } else {
            ClientModel::editVisitType($values['originaltype'], $values['newtype']);
            NotifyModel::addNotify(array('text' => 'Úspěšně změněn typ schůzek', 'class' => 'success'));
            $this->redirect('Settings:clients');
        }
    }


    // Delete
    protected function createComponentDelVisitTypeForm()
    {
        $VisitTypes = ClientModel::getVisitsTypes();
        $array = array();
        foreach ($VisitTypes as $key => $id) {
            $array[$id['id']] = $id['type'];
        }
        $form = new AppForm();
        $form->addSelect('type', 'Typ schůzek', $array);
        $form->addSubmit('submit', 'Odstranit');
        $form->onSubmit[] = callback($this, 'delVisitTypeSubmitted');
        return $form;
    }

    public function DelVisitTypeSubmitted ($form)
    {
        $values = $form->getValues();
        ClientModel::delVisitType($values['type']);
        NotifyModel::addNotify(array('text' => 'Úspěšně změněn typ schůzek', 'class' => 'success'));
        $this->redirect('Settings:clients');
    }

    /* Tags form component */
    // Add
    public function addTagsFormSubmitted($form)
    {
        $values = $form->getValues();
        if (empty($values['tagname'])) {
            NotifyModel::addNotify(array('text' => 'Chyba při přidání tagu', 'class' => 'error'));
        } else {
            ClientModel::tagManagement('add', 0, $values['tagname']);
            NotifyModel::addNotify(array('text' => 'Tag byl úspěšně přidán', 'class' => 'success'));
            $this->redirect('Settings:clients');
        }
    }

    protected function createComponentAddTagsForm()
    {
        $form = new AppForm();
        $form->addText('tagname', 'Název tagu');
        $form->addSubmit('submit', 'Přidat');
        $form->onSubmit[] = callback($this, 'addTagsFormSubmitted');
        return $form;
    }
    

    // Edit
    protected function createComponentEditTagsForm()
    {
        $Tags = ClientModel::getTags();
        $array = array();
        foreach ($Tags as $key => $id) {
            $array[$id['id']] = $id['name'];
        }
        $form = new AppForm();
        $form->addSelect('originaltag', 'Tag', $array);
        $form->addText('tagname', 'Přejmenovat na');
        $form->addSubmit('submit', 'Uložit');
        $form->onSubmit[] = callback($this, 'editTagsFormSubmitted');
        return $form;
    }
    
    public function editTagsFormSubmitted($form)
    {
        $values = $form->getValues();
        if (empty($values['tagname'])) {
            NotifyModel::addNotify(array('text' => 'Chyba při změně tagu', 'class' => 'error'));
        } else {
            ClientModel::tagManagement('add', 0, $values['tagname']);
            NotifyModel::addNotify(array('text' => 'Tag byl úspěšně změněn', 'class' => 'success'));
        }
        $this->redirect('Settings:clients');
    }

    // Delete
    public function delTagsFormSubmitted($form)
    {
        $values = $form->getValues();
        ClientModel::tagManagement('del', $values['deltag'], 0);
        NotifyModel::addNotify(array('text' => 'Tag byl úspěšně odstraněn', 'class' => 'success'));
        $this->redirect('Settings:clients');
    }

    protected function createComponentDelTagsForm()
    {
        $Tags = ClientModel::getTags();
        $array = array();
        foreach ($Tags as $key => $id) {
            $array[$id['id']] = $id['name'];
        }
        
        $form = new AppForm();
        $form->addSelect('deltag', 'Smazat tag', $array);
        $form->addSubmit('submit', 'Smazat');
        
        $form->onSubmit[] = callback($this, 'delTagsFormSubmitted');
        return $form;
    }

    /* Groups form components */
    protected function createComponentAddGroupForm()
    {
        $form = new AppForm();
        $form->addText('name', 'Název skupiny');
        $form->addSelect('color', 'Barva skupiny', BaseModel::getColors());
        $form->addText('description', 'Popis skupiny');
        $form->addText('weight', 'Váha');
        $form->addSubmit('submit', 'Přidat');
        $form->onSubmit[] = callback($this, 'addGroupFormSubmitted');
        
        return $form;
    }

    public function addGroupFormSubmitted($form)
    {
        $values = $form->getValues();
        if (empty($values['name'])) {
            NotifyModel::addNotify(array('text' => 'Nelze přidat skupinu, je nutné zadat její název', 'class' => 'error'));
        } else {
            ClientModel::groupManagement('add', 0, $values['name'], $values['color'], $values['description'], $values['weight']);
            NotifyModel::addNotify(array('text' => 'Skupina byla úspěšně přidána', 'class' => 'success'));
        }
        $this->redirect('Settings:clients');
    }

    protected function createComponentEditGroupForm()
    {
        foreach (ClientModel::getGroups() as $key => $id) {
            $array[$id['id']] = $id['name'];}
        $form = new AppForm;
        $form->addSelect('name', 'Skupina', $array);
        $form->addText('newname', 'Nový název');
        $form->addSelect('color', 'Barva skupiny', BaseModel::getColors());
        $form->addText('description', 'Popis skupiny');
        $form->addText('weight', 'Váha');
        $form->addSubmit('submit', 'Uložit');
        $form->onSubmit[] = callback($this, 'editGroupFormSubmitted');
        return $form;
    }

    public function editGroupFormSubmitted($form) {
        $values = $form->getValues();
        if (empty($values['newname'])) {
            NotifyModel::addNotify(array('text' => 'Nelze mít skupinu bez názvu', 'class' => 'error'));
        } else {
            ClientModel::groupManagement('edit', $values['name'], $values['newname'], $values['color'], $values['description'], $values['weight']);
            NotifyModel::addNotify(array('text' => 'Skupina byla změněna', 'class' => 'success'));
            $this->redirect('Settings:clients');
        }
    }
    
    protected function createComponentDelGroupForm()
    {
        foreach (ClientModel::getGroups() as $key => $id) {
            $array[$id['id']] = $id['name'];
        }
        $form = new AppForm;
        $form->addSelect('name', 'Název skupiny', $array);
        $form->addRadioList('deleteclients', 'Klienty', array('true' => 'Smazat', 'false' => 'Přesunout'))->setDefaultValue('true');
        $form->addSelect('moveto', 'Přesunout do skupiny', $array);
        $form->addSubmit('submit', 'Smazat');
        $form->onSubmit[] = callback($this, 'delGroupFormSubmitted');
        return $form;
    }

    public function delGroupFormSubmitted($form) {
        $values = $form->getValues();
        if ($values['deleteclients'] == 'true') { 
            ClientModel::clientsManagement('groupdel', $values['name']);
            ClientModel::groupManagement('del', $values['name']);
            NotifyModel::addNotify(array('text' => 'Skupina úspěšně smazána a kontakty byly odstraněny', 'class' => 'success'));
        } else {
            if ($values['name'] == $values['moveto']) {
                NotifyModel::addNotify(array('text' => 'Nelze přesunout kontakty do stejné skupiny!', 'class' => 'error'));
            } else {
                ClientModel::clientsManagement('groupmove', $values['name'], $values['moveto']);
                ClientModel::groupManagement('del', $values['name']);
                NotifyModel::addNotify(array('text' => 'Skupina úspěšně smazána a kontakty byly přesunuty', 'class' => 'success'));
            }
        }
        $this->redirect('Settings:clients');
    }
}

