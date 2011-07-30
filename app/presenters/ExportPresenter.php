<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * ExportPresenter
 *
 * @author     eWide
 * @package    eWide Client
 */

class ExportPresenter extends BasePresenter
{
    public function renderDefault()
    {
        
    }

    /* Form to show records in date range */
    protected function createComponentExportForm()
    {
        $form = new AppForm();
        $form->addSelect('format', 'Formát', array('sql' => 'SQL'));
        $form->addSubmit('submit', 'Exportovat')->getControlPrototype()->class("normal-button inline");
        
        $form->onSubmit[] = callback($this, 'exportFormSubmitted');
        
        return $form;
    }
    
    public function exportFormSubmitted($form)
    {
        $values = $form->getValues();
        switch($values['format']) {
            case 'sql':
                $file = ExportModel::exportSql();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                header("Content-Transfer-Encoding: binary");
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"eWide-".date("Y-m-d").".sql\"");
                echo $file;
                die();
                break;
        }
    }
}
