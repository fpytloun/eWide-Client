<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Users authenticator.
 *
 * @author     eWide
 * @package    eWide Client
 */

class UsersModel extends Object implements IAuthenticator
{

    /**
     * Performs an authentication
     * @param  array
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        $username = $credentials[self::USERNAME];
        $password = sha1($credentials[self::PASSWORD]);

        $row = dibi::fetch('SELECT * FROM users WHERE login=%s AND active=%b', $username, 1);

        if (!$row) {
            throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        }

        if ($row->password !== $password) {
            throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }

        unset($row->password);
            
        return new Identity($row->id, $row->role, $row);
    }

    public static function getUser($id)
    {
        $query = dibi::query("SELECT * FROM `users` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchAll();

        return $result[0];
    }

    public static function getUsers()
    {
        $query = dibi::query("SELECT * FROM `users`");
        $query = $query->fetchAll();
        return $query;
    }
    
    public static function addUser($values)
    {
        $query = dibi::query("INSERT INTO users(`login`,`password`,`role`,`email`,`phone`,`created`) VALUES(%s,%s,%s,%s,%s,NOW())", $values['login'], sha1($values['password']), $values['role'], $values['email'], $values['phone']);
        
        return $query;
    }
    
    public static function editUser($values)
    {
        if ($values['delete'] == true) {
            $query = dibi::query("DELETE FROM `users` WHERE `id` = %i", $values['id']);
        } else {
            if (isset($values['password'])) {
                $query = dibi::query("UPDATE `users` SET `login` = %s, `password` = %s, `role` = %s, `email` = %s, `phone` = %s WHERE `id` = %i", $values['login'], $values['password'], $values['role'], $values['email'], $values['phone'], $values['id']);
            } else {
                $query = dibi::query("UPDATE `users` SET `login` = %s, `role` = %s, `email` = %s, `phone` = %s WHERE `id` = %i", $values['login'], $values['role'], $values['email'], $values['phone'], $values['id']);
            }
        }
        
        return $query;
    }
}
