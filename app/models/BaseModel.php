<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Base model
 *
 * @author     eWide
 * @package    eWide Client
 */

class BaseModel
{
    public static function getColors()
    {
        $colors = array( 'transparent' => 'Žádná',
                         'FFF9D7' => 'Béžová',
                         '60CEDE' => 'Modrá',
                         'FDFF6C' => 'Žlutá',
                         '8AF15D' => 'Zelená',
                         'BEBEBE' => 'Černá',
                         'F1807A' => 'Červená' );
        return $colors;
    }
    
    public static function getMonths()
    {
        $months = array( '1' => 'Leden',
                         '2' => 'Únor',
                         '3' => 'Březen',
                         '4' => 'Duben',
                         '5' => 'Květen',
                         '6' => 'Červen',
                         '7' => 'Červenec',
                         '8' => 'Srpen',
                         '9' => 'Září',
                         '10' => 'Říjen',
                         '11' => 'Listopad',
                         '12' => 'Prosinec' );
        return $months;
    }
}

