<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Login / logout presenters.
 *
 * @author     eWide
 * @package    eWide Client
 */

class LoginPresenter extends BasePresenter
{

    protected function beforeRender() {
        $this->setLayout('layout-login');
        $user = Environment::getUser();
        
        if ($user->isLoggedIn()) {
            $user->logout(TRUE);
        }
    }

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm()
    {
        $form = new AppForm($this, 'loginForm');
        $form->setTranslator($this->translator);
        $form->addText('username', 'Username:')
            ->addRule(AppForm::FILLED, 'Please provide a username.');

        $form->addPassword('password', 'Password:')
            ->addRule(AppForm::FILLED, 'Please provide a password.');

        $form->addCheckbox('remember', 'Remember me on this computer');

        $form->addSubmit('login', 'Login');

        $form->onSubmit[] = callback($this, 'loginFormSubmitted');
        return $form;
    }



    public function loginFormSubmitted($form)
    {
        try {
            $values = $form->values;
            if ($values['remember']) {
                $this->getUser()->setExpiration('+ 14 days', FALSE);
            } else {
                $this->getUser()->setExpiration('+ 20 minutes', TRUE);
            }
            $this->getUser()->login($values['username'], $values['password']);
            
            // Notify today visits
            $visits = ClientModel::getVisits(null, date('Y-m-d'), date('Y-m-d'));
            $visits_undone = 0;
            
            foreach ($visits as $key => $visit) {
                if ($visit['done'] == 0) {
                    $visits_undone++;
                }                    
            }
            
            if ($visits_undone > 0) {
                $text = "<p>Vítejte, jste přihlášen jako ".$values['username'].".</p><p>Dnes máte naplánovaných <span style='color: #3484D2;font-weight:bold;'>".$visits_undone."</span> schůzek.</p><div class='button' style='margin-left:auto;margin-right:auto;'><a href='".$this->link('Overview:visits', array('date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')))."'>Zobrazit</a></div>";
                NotifyModel::addNotify(array('text' => $text));
            }
            
            $this->redirect('Homepage:');

        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}
