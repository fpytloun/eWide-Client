<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Settings model
 *
 * @author     eWide
 * @package    eWide Client
 */
 
class SettingsModel
{
    public static function getValue($property,$userid = null, $explode = true, $hash = false)
    {
        // Get user setting first if exists
        if (!empty($userid)) {
            $value = dibi::query("SELECT `value` FROM `users_settings` WHERE `property` = %s AND `user` = %i LIMIT 1", $property, $userid);
            $result = $value->fetchSingle();
        }
        
        // If not, get global setting
        if (empty($result)) {
            $value = dibi::query("SELECT `value` FROM `settings` WHERE `property` = %s LIMIT 1", $property);
            $result = $value->fetchSingle();
        }
        
        $result = ($explode == true) ? explode(",", $result) : $result;
        
        if ($explode == true && $hash == true) {
            $tmp = null;
            foreach ($result as $key => $value) {
                $new_key = explode('=>', $value);
                $tmp[$new_key[0]] = $new_key[1];
            }
            $result = $tmp;
        }

        
        if (sizeof($result) <= 1) {
            $result = $result[0];
        }
        return $result;
     }
     
    public static function saveSettings($values, $userid = "")
    {
        $user_settings = array('login', 'email', 'phone', 'password', 'role');
         foreach ($values as $property => $value) {
             if (!in_array($property, $user_settings)) {
                 if (!empty($userid)) {
                     // Local setting
                     $tmp = dibi::query("SELECT `value` FROM `users_settings` WHERE `user` = %i AND `property` = %s", $userid, $property);
                     if ($tmp->rowCount != 0) {
                         // Update existing
                         dibi::query("UPDATE `users_settings` SET `value` = %s WHERE `user` = %i AND `property` = %s", $value, $userid, $property);
                     } else {
                         // Insert new if value is not the same as already set global one
                         $global = self::getValue($property);
                         if ($global != $value) {
                            dibi::query("INSERT INTO users_settings(`user`,`property`,`value`) VALUES(%i,%s,%s)", $userid, $property, $value);
                        }
                     }
                 } else {
                     // Global setting
                     dibi::query("UPDATE `settings` SET `value` = %s WHERE `property` = %s", $value, $property);
                 }
            } else {
                dibi::query("UPDATE `users` SET `".$property."` = %s WHERE `id` = %s", $value, $userid);
            }
         }
     } 
 }
 
