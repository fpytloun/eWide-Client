<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Homepage presenter.
 *
 * @author     eWide
 * @package    eWide Client
 */

class HomepagePresenter extends BasePresenter
{
    /** @persistent */
    public $group = 0;
    
    /** @persistent */
    public $order = 'id';
    
    /** @persistent */
    public $sort = 'asc';
    
    /** @persistent */
    public $search;
    
    /** @persistent */
    public $tags;
    
    /** @persistent */
    public $groups;
    
    /** @persistent */
    public $columns;
    
    public $clientGroups;
    
    public function createComponentVp()
    {
        return new VisualPaginator;
    }

    public function renderDefault()
    {
        /* Show and search clients */
        $orderOptions = array(
            'id' => 'Data přidání',
            'name' => 'Firmy [a-z]',
            'contact_person' => 'Kontaktní osoby [a-z]',
            'last_visit' => 'Data poslední návštěvy',
            'last_order' => 'Data poslední objednávky',
            'last_order_price' => 'Ceny objednávky',
            'group_name' => 'Skupiny [a-z]'
        );
        
        $this->clientGroups = ClientModel::getGroups();
        
        $this->group = (empty($this->params['group'])) ? $this->group : $this->params['group'];
        $this->order = (empty($this->params['order'])) ? $this->order : $this->params['order'];
        $this->sort = (empty($this->params['sort'])) ? $this->sort : $this->params['sort'];
        $this->search = (empty($this->params['search'])) ? $this->search : $this->params['search'];
        $this->tags = (empty($this->params['tags'])) ? $this->tags : $this->params['tags'];

        // Search parameters for ClientModel::getClients()
        $search = array('string' => $this->search, 'tags' => $this->tags, 'groups' => $this->groups);
        
        // Get clients and filter them
        $clients = ClientModel::getClients($this->group, $this->user->id, $search);

        /* VisualPaginator */
        $vp = $this["vp"];
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = SettingsModel::getValue('clients_per_page');
        $paginator->itemCount = sizeof($clients);

        $this->template->coloredId = SettingsModel::getValue('colored_id', $this->user->id);
        $this->template->showBotMenu = SettingsModel::getValue('show_bot_menu', $this->user->id);
        $this->template->clients = array_slice(ClientModel::orderBy($clients, $this->order, $this->sort), $paginator->offset, $paginator->itemsPerPage);
        $this->template->clientsCount = $paginator->itemCount;
        $this->template->clientGroups = $this->clientGroups;
        $this->template->selectedGroup = $this->group;
        $this->template->selectedOrder = $this->order;
        $this->template->selectedSort = $this->sort;
        $this->template->selectedSearch = $this->search;
        $this->template->orderOptions = $orderOptions;
        
        if (empty($this->columns) || $this->columns == '*') {
            $this->template->tableColumns = SettingsModel::getValue('show_columns', $this->user->id, true, true);
        } else {
             $all_columns = SettingsModel::getValue('all_columns', $this->user->id, true, true);
             $show_columns = explode(',', $this->columns);
             $show = null;
             
             foreach ($show_columns as $key => $value) {
                 $show[$value] = $all_columns[$value];
             }
             
             $this->template->tableColumns = $show;
         }
    }
    
    /* Form searchClients component */
    public function searchClientsFormSubmitted($form)
    {
        $values = $form->getValues();
        
        foreach ($values['tags'] as $key => $value) {
            if ($value == true) {
                $tags[] = $key;
            }
        }
        
        foreach ($values['groups'] as $key => $value) {
            if ($value == true) {
                $groups[] = $key;
            }
        }
        
        $all_columns = SettingsModel::getValue('all_columns', $this->user->id, true, true);
        foreach ($values['columns'] as $key => $value) {
            if ($value == true) {
                $columns[] = $key;
            }
        }

        $this->redirect('Homepage:default', array('group' => '0', 'order' => $this->order, 'sort' => $this->sort, 'search' => $values['phrase'], 'tags' => implode(',', $tags), 'groups' => implode(',', $groups), 'columns' => implode(',', $columns)));
    }
    
    protected function createComponentFormSearchClients()
    {
        $tagNames = ClientModel::getTags();
        $clientGroups = ClientModel::getGroups();
        
        $form = new AppForm();
        // Text
        $form->addGroup('Text');
        $form->addText('phrase')->setDefaultValue(($this->search != '*') ? $this->search : null);
        
        // Tags
        if (!empty($tagNames)) {
            $form->addGroup('Tagy');
            $tagslist = $form->addContainer('tags');
            foreach ($tagNames as $key => $name) {
                $tagslist->addCheckbox($name['id'], $name['name'])->setDefaultValue((in_array($name['id'], explode(',', $this->tags))) ? true : false);
            }
        }
        
        // Groups
        if (!empty($clientGroups)) {
            $form->addGroup('Skupiny');
            $grouplist = $form->addContainer('groups');
            foreach ($clientGroups as $key => $name) {
                $grouplist->addCheckbox($name['id'], $name['name'])->setDefaultValue((in_array($name['id'], explode(',', $this->groups))) ? true : false);
            }
        }
        
        // Columns
        $form->addGroup('Zobrazené sloupce');
        $columnslist = $form->addContainer('columns');
        $show_columns = SettingsModel::getValue('show_columns', $this->user->id, true, true);
        $all_columns = SettingsModel::getValue('all_columns', $this->user->id, true, true);
        foreach ($all_columns as $key => $name) {
            $columnslist->addCheckbox($key, $name)->setDefaultValue((in_array($key, array_flip($show_columns)) || in_array($key, explode(',', $this->columns))) ? true : false);
        }
        
        $form->setCurrentGroup(null);
        $form->addSubmit('submit', 'Hledat');
        $form->onSubmit[] = callback($this, 'searchClientsFormSubmitted');
        return $form;
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
        
        if (SettingsModel::getValue('goto_added_client', $this->user->id) == 1) {
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
