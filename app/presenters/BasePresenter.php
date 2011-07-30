<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Base class for all application presenters.
 *
 * @author     eWide
 * @package    eWide Client
 */

abstract class BasePresenter extends Presenter
{    
    public $user;
    public $translator;
    public $identity;
    
    public function __construct()
    {
        $this->user = Environment::getUser();
        $this->identity = $this->user->getIdentity();

        if (!empty($this->identity->lang)) {
            $lang = SettingsModel::getValue("language", $this->user->getId());
        } else {
            $lang = SettingsModel::getValue("language");
        }

        $this->translator = new GettextTranslator(APP_DIR."/lang/".$lang.".mo");
        $this->template->setTranslator(new GettextTranslator(APP_DIR."/lang/".$lang.".mo"));
    }
    
    protected function beforeRender()
    {
        // If user is not logged in or not on Login presenter, redirect him to login presenter
        if (!$this->user->isLoggedIn() && $this->getName() != "Login" && $this->getName() != "Error") {
            $this->redirect("Login:default");
        }
        $this->template->theme = SettingsModel::getValue('theme');
        $this->template->isAdmin = $this->user->isInRole('admin');
        
        $notify = NotifyModel::getNotify();
        NotifyModel::delNotify();
        if (!empty($notify)) {
            $this->template->notify = $notify;
        }
    }
}
