<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 02:41
 */

namespace wfw\engine\lib\data\date;

/**
 *  Permet de formatter des dates
 */
class DateFormater
{
    /**
     *  Format d'heure sur 12h
     */
    public const TIME_FORMAT_12=0;
    /**
     *  Format d'heure sur 24h
     */
    public const TIME_FORMAT_24=1;

    public const MYSQL_FORMAT="Y-m-d H:i:s";
    public const HTML_INPUT_FORMAT="Y-m-d";

    private $_date;

    /**
     *  DateFormater constructor.
     *
     * @param Date $date Date à formatter
     */
    public function __construct(Date $date){
        $this->_date = $date;
    }

    /**
     *  Retourne la date courante formatée
     *
     * @param string $format Fromat de la date
     *
     * @return string
     */
    public function getFormat(string $format):string{
        return $this->_date->format($format);
    }
}