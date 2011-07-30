<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Notify model
 *
 * @author     eWide
 * @package    eWide Client
 */
 
class NotifyModel
{
    public static function addNotify($args = array())
    {
        $_SESSION['notify']['text'] = (isset($args['text'])) ? $args['text'] : null;
        $_SESSION['notify']['class'] = (isset($args['class'])) ? $args['class'] : null;
    }
    
    public static function getNotify()
    {
        if (isset($_SESSION['notify'])) {
            return $_SESSION['notify'];
        } else {
            return false;
        }
    }
    
    public static function delNotify()
    {
        unset($_SESSION['notify']);
        return true;
    } 
}

