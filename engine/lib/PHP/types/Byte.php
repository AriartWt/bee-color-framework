<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 11:33
 */

namespace wfw\engine\lib\PHP\types;

/**
 * Permet de manipuler des valeurs d'octets.
 */
final class Byte
{
    public const Ko = "ko";
    public const Mo = "mo";
    public const Go = "go";
    public const To = "to";
    public const Po = "po";

    private const POWS = [
        self::Ko => 1,
        self::Mo => 2,
        self::Go => 3,
        self::To => 4,
        self::Po => 5
    ];

    /** @var int $_byte */
    private $_byte;

    /**
     * Byte constructor.
     *
     * @param string $bytes Nombre d'octets accompagné ou non d'une notation (ko,mo,go,to,po)
     */
    public function __construct(string $bytes) {
        $len = strlen($bytes);
        if($len > 2){
            $number = substr($bytes,0,$len-2);
            $unit = strtolower(substr($bytes,$len-2,2));
            if(isset(self::POWS[$unit])){
                $this->_byte = floatval($number) * $this->pow(self::POWS[$unit]);
            }else{
                $this->_byte = floatval($number);
            }
        }else{
            $this->_byte = floatval($bytes);
        }
    }

    /**
     * @param int $val Puissance de 1024 souhaitée
     * @return int
     */
    private function pow(int $val){
        $res = 1;
        while($val > 0){ $res *= 1024; $val--;}
        return $res;
    }

    /**
     * @return int
     */
    public function toInt():int{
        return $this->_byte;
    }

    /**
     * Renvoie la plus petite notation entiere possible.
     * @return string
     */
    public function toShortestNotation():string{
        $res = $this->_byte;
        $currentPow = '';
        foreach (self::POWS as $POW) {
            if($res % 1024 === 0){ $res /= 1024; $currentPow = $POW; }
            else break;
        }
        return $res.$currentPow;
    }

    /**
     * @param string $notation
     * @return string
     */
    public function toNotation(string $notation):string{
        $notation = strtolower($notation);
        if(!isset(self::POWS[$notation]))
            throw new \InvalidArgumentException("Unknown notation $notation");
        $n = self::POWS[$notation];
        $res = $this->_byte;
        while($n > 0){$res /= 1024; $n--;}
        return $res.$notation;
    }

    /**
     * @param Byte $byte
     * @return int
     */
    public function compareTo(Byte $byte):int{
        return $this->_byte - $byte->toInt();
    }
}