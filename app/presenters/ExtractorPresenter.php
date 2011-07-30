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

class ExtractorPresenter extends BasePresenter
{
    public function renderDefault()
    {
        // If user is admin and we are not in production environment
        if ($this->user->isInRole("admin") && !Environment::isProduction()) {
            $ge = new NetteGettextExtractor();

            $ge->setupForms();
            $ge->setupDataGrid();
                    
            $ge->scan(APP_DIR);
            $ge->save(APP_DIR . '/lang/application.po');
        }
    }
}
