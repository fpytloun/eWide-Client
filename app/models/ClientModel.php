<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Client model
 *
 * @author     eWide
 * @package    eWide Client
 */
 

class ClientModel
{    
    public static function getClients($group = null, $userid = null, $search = null)
    {
        if ($group != null) {
            $query = dibi::query("SELECT
                                   clients.*,
                                   `clients_groups`.`name` AS `group_name`,
                                   `clients_groups`.`color` AS `group_color`,
                                   `clients_persons`.`name` AS `contact_person`,
                                   `clients_persons`.`phone` AS `contact_phone`,
                                   `clients_persons`.`email` AS `contact_email`
                                FROM
                                   clients
                                LEFT JOIN (clients_groups) ON (clients_groups.id = clients.group)
                                LEFT JOIN (clients_persons) ON (clients_persons.client = clients.id AND clients_persons.main = 1)
                                WHERE `clients`.`group` = %i", $group
                                );
        } else {
            $query = dibi::query("SELECT
                                   clients.*,
                                   `clients_groups`.`name` AS `group_name`,
                                   `clients_groups`.`color` AS `group_color`,
                                   `clients_persons`.`name` AS `contact_person`,
                                   `clients_persons`.`phone` AS `contact_phone`,
                                   `clients_persons`.`email` AS `contact_email`
                                FROM
                                   clients
                                LEFT JOIN (clients_groups) ON (clients_groups.id = clients.group)
                                LEFT JOIN (clients_persons) ON (clients_persons.client = clients.id AND clients_persons.main = 1)"
                                );
        }
        
        $result = array();
        while ($client = $query->fetch()) {
            $last_visit_done = ClientModel::getLastVisit($client['id'], true);
            $last_visit_undone = ClientModel::getLastVisit($client['id'], false);
            $last_order = ClientModel::getLastOrder($client['id']);

            $client['last_visit'] = (empty($last_visit_done['date'])) ? '' : date(SettingsModel::getValue('date_format', $userid), strtotime($last_visit_done['date']));
            $client['last_visit_notes'] = $last_visit_done['notes'];

            $client['last_visit_undone'] = (empty($last_visit_undone['date'])) ? '' : date(SettingsModel::getValue('date_format', $userid), strtotime($last_visit_undone['date']));
            $client['last_visit_undone_notes'] = $last_visit_undone['notes'];

            $client['last_order'] = (empty($last_order['date'])) ? '' : date(SettingsModel::getValue('date_format', $userid), strtotime($last_order['date']));
            $client['last_order_price'] = (empty($last_order['date'])) ? '' : PriceModel::render($last_order['price']);
            $client['added'] = (empty($client['added'])) ? '' : date(SettingsModel::getValue('date_format', $userid), strtotime($client['added']));
            $client['contact_person'] = (empty($client['contact_person'])) ? '---' : $client['contact_person'];
            $client['phone'] = (empty($client['phone'])) ? $client['contact_phone'] : $client['phone'];
            $client['email'] = (empty($client['email'])) ? $client['contact_email'] : $client['email'];
            
            $noMinus = array('psc', 'city');
            foreach ($client as $key => $value) {
                if (!in_array($key, $noMinus)) {
                    $client[$key] = (empty($value)) ? '---' : $value;
                }
            }
            
            // Fulltext and tags search
            if ($search != null) {
                $string = ($search['string'] == '*') ? NULL : self::removeDiacritics($search['string']);
                $tags = ($search['tags'] == '*') ? NULL : $search['tags'];
                $groups = ($search['groups'] == '*') ? NULL : explode(',', $search['groups']);
                
                // Check if client has searched tags
                if ($tags != NULL) {
                    $tagsmatch = self::checkTagsMatch(explode(',', $client['tags']), explode(',', $tags));
                } else {
                    $tagsmatch = true;
                }
                
                // Check if client has searched groups
                if ($groups != NULL) {
                    $groupmatch = (in_array($client['group'], $groups)) ? true : false;
                } else {
                    $groupmatch = true;
                }
                
                // Fulltext search if tags match
                if ($tagsmatch == true && $groupmatch == true) {
                    // ... and if we have search string
                    if ($string != NULL) {
                        $pos = false;
                        foreach ($client as $key => $value) {
                            if ($pos != true) {
                                if (stripos($value, $string) !== false) {
                                    $pos = true;
                                }
                            }
                        }
                        
                        // Fulltext search match, let's add the client
                        if ($pos != false) {
                            $result[] = $client;
                        }
                    } else {
                        $result[] = $client;
                    }
                }
            }
        }
        
        return $result;
    }
    
    public static function getClient($id)
    {
        $query = dibi::query("SELECT * FROM `clients` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchAll();

        return $result[0];
    }
    
    public static function getOrders($id = null, $date_from = null, $date_to = null)
    {
        if (isset($id)) {
            if (isset($date_from) && isset($date_to)) {
                $query = dibi::query("SELECT `clients_orders`.*, `clients`.`name` AS `client_name` FROM `clients_orders` LEFT JOIN (clients) ON (clients.id = clients_orders.client) WHERE `client` = %i AND `date` >= %s AND `date` <= %s ORDER BY `date` DESC", $id, $date_from, $date_to);
            } else {
                $query = dibi::query("SELECT `clients_orders`.*, `clients`.`name` AS `client_name` FROM `clients_orders` LEFT JOIN (clients) ON (clients.id = clients_orders.client) WHERE `client` = %i ORDER BY `date` DESC", $id);
            }
        } else {
            if (isset($date_from) && isset($date_to)) {
                $query = dibi::query("SELECT `clients_orders`.*, `clients`.`name` AS `client_name` FROM `clients_orders` LEFT JOIN (clients) ON (clients.id = clients_orders.client) WHERE `date` >= %s AND `date` <= %s ORDER BY `date` DESC", $date_from, $date_to);
            } else {
                $query = dibi::query("SELECT `clients_orders`.*, `clients`.`name` AS `client_name` FROM `clients_orders` LEFT JOIN (clients) ON (clients.id = clients_orders.client) ORDER BY `date` DESC");
            }
        }
        $result = $query->fetchAll();

        return $result;
    }
    
    public static function getVisits($id = NULL, $date_from = null, $date_to = null)
    {
        if (isset($id)) {
            if (isset($date_from) && isset($date_to)) {
                $query = dibi::query("SELECT `clients_visits`.*, `clients`.`name` AS `client_name`, `clients_visits_types`.`type` AS `type_name` FROM `clients_visits` LEFT JOIN (clients, clients_visits_types) ON (clients.id = clients_visits.client AND clients_visits_types.id = clients_visits.type) WHERE `client` = %i AND `date` >= %s AND `date` <= %s ORDER BY `date` DESC", $id, $date_from, $date_to);
            } else {
                $query = dibi::query("SELECT `clients_visits`.*, `clients`.`name` AS `client_name`, `clients_visits_types`.`type` AS `type_name` FROM `clients_visits` LEFT JOIN (clients, clients_visits_types) ON (clients.id = clients_visits.client AND clients_visits_types.id = clients_visits.type) WHERE `client` = %i ORDER BY `date` DESC", $id);
            }
        } else {
            if (isset($date_from) && isset($date_to)) {
                $query = dibi::query("SELECT `clients_visits`.*, `clients`.`name` AS `client_name`, `clients_visits_types`.`type` AS `type_name` FROM `clients_visits` LEFT JOIN (clients, clients_visits_types) ON (clients.id = clients_visits.client AND clients_visits_types.id = clients_visits.type) WHERE `date` >= %s AND `date` <= %s ORDER BY `date` DESC", $date_from, $date_to);
            } else {
                $query = dibi::query("SELECT `clients_visits`.*, `clients`.`name` AS `client_name`, `clients_visits_types`.`type` AS `type_name` FROM `clients_visits` LEFT JOIN (clients, clients_visits_types) ON (clients.id = clients_visits.client AND clients_visits_types.id = clients_visits.type) ORDER BY `date` DESC");
            }
        }
        $result = $query->fetchAll();

        return $result;
    }
    
    public static function getPersons($id)
    {
        $query = dibi::query("SELECT * FROM `clients_persons` WHERE `client` = %i ORDER BY `main` DESC", $id);
        $result = $query->fetchAll();

        return $result;
    }
    
    public static function toggleVisits($values)
    {
        // Get visits
        $visits = self::getVisits();
        foreach($visits as $key => $visit) {
            if ($values['visit_'.$visit['id']] == true) {
                $query = ($values['action'] == 'delete') ? dibi::query("DELETE FROM `clients_visits` WHERE `id` = %i", $visit['id']) : dibi::query("UPDATE `clients_visits` SET `done` = %b WHERE `id` = %i", true, $visit['id']);
            }
        }
    }
    
    public static function getTagsArray($tags)
    {
        $tags = explode(',', $tags);
        
        return $tags;
    }
    
    public static function getTags()
    {
        $query = dibi::query("SELECT * FROM `clients_tags`");
        $result = $query->fetchAll();
        
        if ($query->rowCount() > 0) {
            return $result;
        } else {
            return false;
        }
    }
    
    public static function getVisitsTypes()
    {
        $query = dibi::query("SELECT * FROM `clients_visits_types`");
        $result = $query->fetchAll();
        
        if ($query->rowCount() > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public static function addVisitType($type)
    {
        dibi::query('INSERT INTO clients_visits_types(`type`) VALUES(%s)', $type);
    }
    
    public static function editVisitType($id, $type)
    {
        dibi::query('UPDATE `clients_visits_types` SET `type` = %s WHERE id = %i', $type, $id);
    }

    public static function delVisitType($id)
    {
        dibi::query('DELETE FROM `clients_visits_types` WHERE id = %i LIMIT 1', $id);
    }

    public static function removeDiacritics($string)
    {
        $string = str_replace(array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý ","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž"), array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y ","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z"), $string);
        return $string;
    }
    
    /**
     * This method is deprecated because it's too slow.
     * Please use getClients() method if you can.
     */
    public static function search($clients, $string = NULL, $tags = NULL)
    {
        $string = ($string == '*') ? NULL : self::removeDiacritics($string);
        $tags = ($tags == '*') ? NULL : $tags;
        $clients_new = array();
        foreach ($clients as $num => $client) {
            // Check if client has searched tags
            if ($tags != NULL) {
                $tagsmatch = self::checkTagsMatch(explode(',', $client['tags']), explode(',', $tags));
            } else {
                $tagsmatch = true;
            }
            
            // Fulltext search if tags match
            if ($tagsmatch == true) {
                /// ... and if we have search string
                if ($string != NULL) {
                    $pos = false;
                    foreach ($client as $key => $value) {
                        $pos = ($pos != true) ? stripos($value, $string) : true;
                    }
                    
                    // Fulltext search match, let's add the client
                    if ($pos != false) {
                        $clients_new[] = $clients[$num];
                    }
                } else {
                    $clients_new[] = $clients[$num];
                }
            }
        }
        return $clients_new;
    }

    /**
     *    Function for checking tags used for search 
     *    @param array $tags Tags you are searching for
     *    @param array $searchedtags Tags you are searching for
     *    @return bool
     */
    public static function checkTagsMatch($tags, $searchedtags)
    {
        $found = false;
        foreach ($searchedtags as $key => $tag) {
            if (in_array($tag, $tags)) {
                $found = true;
            }
        }
        return $found;
    }

    public static function orderBy($array, $order, $sort)
    {
        $tmp = array();
        
        foreach ($array as $ma) {
            if ($order == 'last_visit' || $order == 'last_order' || $order == 'added') {
                // Fix date
                $tmp[] = date('Y-m-d', strtotime($ma[$order]));
            } elseif ($order == 'last_order_price') {
                // Fix price
                $price = str_replace('.', '', $ma[$order]);
                $price = str_replace(',', '', $price);
                $price = str_replace('-', '', $price);
                $price = str_replace(' ', '', $price);
                $price = str_replace(SettingsModel::getValue('currency'), '', $price);
                $tmp[] = $price;
            } else {
                $tmp[] = $ma[$order];
            }
        }
        
        if ($sort == 'asc') {
            array_multisort($tmp, SORT_ASC, $array);
        } else {
            array_multisort($tmp, SORT_DESC, $array);
        }
        
        return $array;
    }
    
    public static function getGroupName($group)
    {
        $query = dibi::query("SELECT `name` FROM `clients_groups` WHERE `id` = %i LIMIT 1", $group);
        
        $result = $query->fetchSingle();
        
        return $result;
    }
    
    public static function getGroupColor($group)
    {
        $query = dibi::query("SELECT `color` FROM `clients_groups` WHERE `id` = %i LIMIT 1", $group);
        
        $result = $query->fetchSingle();
        
        return $result;
    }
    
    public static function getTypeName($type)
    {
        $query = dibi::query("SELECT `type` FROM `clients_visits_types` WHERE `id` = %i LIMIT 1", $type);
        
        $result = $query->fetchSingle();
        
        return $result;
    }
    
    /* Tags */
    public static function getTagName($id)
    {
        $query = dibi::query("SELECT `name` FROM `clients_tags` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchSingle();
        
        return $result;
    }

    public static function tagManagement($function, $id, $tagname)
    {
        switch ($function) {
            case 'add':
                dibi::query('INSERT INTO clients_tags(`name`) VALUES(%s)', $tagname);
                break;
            case 'del':
                dibi::query('DELETE FROM `clients_tags` WHERE id = %i LIMIT 1', $id);
                break;
            case 'edit':
                dibi::query('UPDATE `clients_tags` SET `name` = %s WHERE id = %i', $tagname, $id);
                break;
        }
    }

    public static function getClientName($id)
    {
        $query = dibi::query("SELECT `name` FROM `clients` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchSingle();
        
        return $result;
    }

    
    public static function getContactPerson($client)
    {
        $query = dibi::query("SELECT * FROM `clients_persons` WHERE `client` = %i AND `main` = %b LIMIT 1", $client, 1);
        $result = $query->fetchAll();
        
        if ($query->rowCount() > 0) {
            return $result[0];
        } else {
            return false;
        }
    }
    
    public static function getLastVisit($client, $done = true)
    {
        $query = dibi::query("SELECT * FROM `clients_visits` WHERE `client` = %i AND `done` = %b ORDER BY `date` DESC LIMIT 1", $client, $done);
        
        $result = $query->fetchAll();
        
        if ($query->rowCount() > 0) {
            return $result[0];
        } else {
            return false;
        }
    }
    
    public static function getLastOrder($client)
    {
        $query = dibi::query("SELECT * FROM `clients_orders` WHERE `client` = %i ORDER BY `date` DESC LIMIT 1", $client);
        
        $result = $query->fetchAll();
        
        if ($query->rowCount() > 0) {
            return $result[0];
        } else {
            return false;
        }
    }

    /* Groups */
    public static function getGroups()
    {
        $query = dibi::query("SELECT * FROM `clients_groups` ORDER BY `weight` DESC");
        
        $groups = $query->fetchAll();
        
        return $groups;
    }

    public static function groupManagement($function, $id, $group = NULL, $color = 'transparent', $description = NULL, $weight = 0)
    {
        switch ($function) {
            case 'add':
                dibi::query('INSERT INTO clients_groups(`name`, `color`, `description`, `weight`, `added`) VALUES(%s, %s, %s, %i, NOW())', $group, $color, $description, $weight);
                break;
            case 'del':
                dibi::query('DELETE FROM `clients_groups` WHERE id = %i LIMIT 1', $id);
                break;
            case 'edit':
                dibi::query('UPDATE `clients_groups` SET `name` = %s, `color`= %s, `description` = %s, `weight` = %i WHERE `id` = %i LIMIT 1', $group, $color, $description, $weight, $id);
                break;
        }
    }
    
    /* Clients */
    public static function addClient($values)
    {
        // Manage tags
        $tags = self::getTags();
        
        if (!empty($tags)) {
            foreach($tags as $key => $tag) {
                if ($values['tag_'.$tag['id']] == true) {
                    $clientTags[] = $tag['id'];
                }
            }
            $tags = implode(',', $clientTags);
        } else {
            $tags = '';
        }
        
        /* Manage contact person */
        if (!empty($values['contact_person'])) {
            // Insert contact person
            $phone = '';
            $email = '';
        } else {
            $phone = $values['phone'];
            $email = $values['email'];
        }
        
        $add_client_query = dibi::query(
            "INSERT INTO clients(`name`,`ic`,`dic`,`street`,`city`,`psc`,`phone`,`email`,`www`,`group`,`tags`,`notes`,`send_emails`,`added`)
            VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%b,NOW())",
            $values['name'], $values['ic'], $values['dic'], $values['street'], $values['city'], $values['psc'], $phone, $email, $values['www'], $values['group'], $tags, $values['notes'], $values['send_emails']
        );
        
        $clientId = dibi::insertId();
        
        if (!empty($values['contact_person'])) {
            $add_person_query = dibi::query(
                "INSERT INTO clients_persons(`client`,`name`,`phone`,`email`,`position`,`main`,`added`)
                VALUES(%i,%s,%s,%s,%s,%b,NOW())",
                $clientId, $values['contact_person'], $values['phone'], $values['email'], $values['position'], 1
            );
        }
        
        return $clientId;
    }
    
    public static function editClient($values)
    {
        if ($values['delete'] == true) {
            // Delete client
            $query = dibi::query("DELETE FROM `clients` WHERE `id` = %i LIMIT 1", $values['id']);
            $query = dibi::query("DELETE FROM `clients_orders` WHERE `client` = %i LIMIT 1", $values['id']);
            $query = dibi::query("DELETE FROM `clients_visits` WHERE `client` = %i LIMIT 1", $values['id']);
            $query = dibi::query("DELETE FROM `clients_persons` WHERE `client` = %i LIMIT 1", $values['id']);
        } else {
            // Manage tags
            $tags = self::getTags();
            foreach($tags as $key => $tag) {
                if ($values['tag_'.$tag['id']] == true) {
                    $clientTags[] = $tag['id'];
                }
            }
            $tags = implode(',', $clientTags);
            
            $query = dibi::query("UPDATE `clients` SET
                `name` = %s, `ic` = %s, `dic` = %s, `street` = %s, `city` = %s, `psc` = %s, `phone` = %s, `email` = %s, `www` = %s, `group` = %i, `tags` = %s, `notes` = %s WHERE `id` = %i",
                $values['name'], $values['ic'], $values['dic'], $values['street'], $values['city'], $values['psc'], $values['phone'], $values['email'], $values['www'], $values['group'], $tags, $values['notes'], $values['id']
            );
        }
    }

    public static function clientsManagement($function, $group, $groupto = 1)
    {
        switch ($function) {
            case 'groupdel':
                dibi::query("DELETE FROM `clients` WHERE `group` = %i", $group);
            case 'groupmove':
                dibi::query("UPDATE `clients` SET `group` = %i WHERE `group` = %i", $groupto, $group);
        }
    }
    
    public static function addVisit($values)
    {
        $query = dibi::query("INSERT INTO clients_visits(`client`, `type`, `date`, `done`, `notes`) VALUES(%i, %i, %s, %b, %s)", $values['client'], $values['type'], $values['date'], $values['done'], $values['notes']);
    }
    
    public static function addOrder($values)
    {
        $query = dibi::query("INSERT INTO clients_orders(`client`, `price`, `date`, `notes`) VALUES(%i, %i, %s, %s)", $values['client'], $values['price'], $values['date'], $values['notes']);
    }
    
    public static function addPerson($values)
    {
        if ($values['main'] == true) {
            // Only one main person is allowed
            $query = dibi::query("UPDATE `clients_persons` SET `main` = %b WHERE `client` = %i", false, $values['client']);
        }
        
        $query = dibi::query("INSERT INTO clients_persons(`client`, `name`, `phone`, `email`, `position`, `main`, `notes`, `added`) VALUES(%i, %s, %s, %s, %s, %b, %s, NOW())",
            $values['client'], $values['name'], $values['phone'], $values['email'], $values['position'], $values['main'], $values['notes']
        );
    }
    
    public static function getVisit($id)
    {
        $query = dibi::query("SELECT * FROM `clients_visits` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchAll();
        
        return $result[0];
    }
    
    public static function getOrder($id)
    {
        $query = dibi::query("SELECT * FROM `clients_orders` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchAll();
        
        return $result[0];
    }
    
    public static function getPerson($id)
    {
        $query = dibi::query("SELECT * FROM `clients_persons` WHERE `id` = %i LIMIT 1", $id);
        $result = $query->fetchAll();
        
        return $result[0];
    }
    
    public static function editVisit($values)
    {
        if ($values['delete'] == true) {
            $query = dibi::query("DELETE FROM `clients_visits` WHERE `id` = %i LIMIT 1", $values['id']);
        } else {
            $query = dibi::query("UPDATE `clients_visits` SET `date` = %s, `type` = %i, `notes` = %s, `done` = %b WHERE `id` = %i", $values['date'], $values['type'], $values['notes'], $values['done'], $values['id']);
        }
    }
    
    public static function editOrder($values)
    {
        if ($values['delete'] == true) {
            $query = dibi::query("DELETE FROM `clients_orders` WHERE `id` = %i LIMIT 1", $values['id']);
        } else {
            $query = dibi::query("UPDATE `clients_orders` SET `date` = %s, `price` = %i, `notes` = %s WHERE `id` = %i", $values['date'], $values['price'], $values['notes'], $values['id']);
        }
    }
    
    public static function editPerson($values)
    {
        if ($values['delete'] == true) {
            $query = dibi::query("DELETE FROM `clients_persons` WHERE `id` = %i LIMIT 1", $values['id']);
        } else {
            if ($values['main'] == true) {
                // Only one main person
                $query = dibi::query("UPDATE `clients_persons` SET `main` = %b WHERE `client` = %i", true, $values['client']);
            }
            $query = dibi::query("UPDATE `clients_persons` SET `name` = %s, `position` = %s, `phone` = %s, `email` = %s, `notes` = %s, `main` = %b WHERE `id` = %i", $values['name'], $values['position'], $values['phone'], $values['email'], $values['notes'], $values['main'], $values['id']);
        }
    }
}
 

