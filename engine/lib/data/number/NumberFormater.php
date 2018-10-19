<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 02:46
 */

namespace wfw\engine\lib\data\number;

/**
 *  Formate l'affichage d'un nombre
 */
class NumberFormater
{
    /**
     *  Limite d'arrondi (ex : $n = 0,2354632 ; $_roundLimit = 2 ; round($n) -> 0.24
     * @var int $_roundLimit
     */
    private $_roundLimit;

    /**
     *  Séparateur de décimaux (2.3 ; 2,3 ...)
     * @var string $_decPoint
     */
    private $_decPoint;

    /**
     *  Séparateur de miliers (100 000 ; 100.000 ; 100,000 ...)
     * @var string $_thousandsSeparator
     */
    private $_thousandsSeparator;

    /**
     *  NumberFormater constructor.
     *
     * @param int    $roundLimit          (optionnel défaut : 2 )  Limite d'arrondi
     * @param string $_decPoint           (optionnel défuat : ".") Séparateur de décimaux
     * @param string $_thousandsSeparator (optionnel défaut : " ") Séparateur de miliers
     */
    public function __construct(int $roundLimit=2,string $_decPoint=".", string $_thousandsSeparator = " ")
    {
        $this->_roundLimit = $roundLimit;
        $this->_decPoint = $_decPoint;
        $this->_thousandsSeparator = $_thousandsSeparator;
    }

    /**
     *  Retourne un nombre arrondi suivant le format $_roundLimit
     *
     * @param mixed $number Nombre à arrondire
     *
     * @return float
     */
    public function round($number):float{
        return round(floatVal($number),$this->_roundLimit);
    }

    /**
     *  Applique le format courant à $number
     *
     * @param mixed $number Nombre à formater
     *
     * @return string
     */
    public function format($number):string{
        return number_format(floatVal($number),$this->_roundLimit,
            $this->_decPoint,
            $this->_thousandsSeparator);
    }

    /**
     *  Formate un nombre d'octets en paquets de la bonne unité
     *
     * @param integer $bytes Nombre d'octets
     *
     * @return string Nombre d'octets formatés
     */
    public function formatOctets(int $bytes):string {
        $units = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $this->_roundLimit) . ' ' . $units[$pow];
    }
}