<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Price model
 *
 * @author     eWide
 * @package    eWide Client
 */
 
class PriceModel
{
    public static function getVat($price)
    {
        $vat = SettingsModel::getValue('vat');
        $price_vat = $price / 100 * $vat;
        return round($price_vat);
    }
    
    public static function getWithVat($price)
    {
        $vat = SettingsModel::getValue('vat');
        $price_vat = $price / 100 * $vat;
        
        $price_with_vat = $price_vat + $price;
        
        return round($price_with_vat);
    }
    
    public static function render($price)
    {
        if (empty($price)) {
            return false;
        }
        $price = round($price);
        $num = strlen($price);
        $price = str_split($price);
        
        $new_price = '';
        $i = 0;
        while ($num >= 0) {
            if (isset($price[$num])) {
                $new_price = $price[$num].$new_price;
            }
            
            if ($i == 3 && $num != 0) {
                $new_price = '.'.$new_price;
                $i = 0;
            }
            
            $num--;
            $i++;
        }
        
        $new_price = $new_price.',- '.SettingsModel::getValue('currency');
        return $new_price;
    }
}

