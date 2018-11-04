<?php 
namespace wfw\engine\lib\data\date;

use DateInterval;
use DateTimeZone;
use OutOfRangeException;
use UnexpectedValueException;

/**
 *  Extension de DateTime pour faciliter l'utilisation des dates en php.
 */
class Date extends \DateTime{
	/**
	 *  Format de la date par défaut
	 * @var string
	 */
	protected $_defaultDateFormat;
	/**
	 *  Format des heures par défaut
	 * @var int
	 */
	protected $_defaultTimeFormat;
	/**
	 *  Date bloquée
	 * @var bool
	 */
	protected $_hold;

	/**
	 *  Date constructor.
	 *
	 * @param string            $str               Représentation
	 * @param DateTimeZone|null $timezone          Timezone
	 * @param null              $defaultDateFormat Formatage de la date (toString)
	 * @param int               $defaultTimeFormat Formatage des heures
	 */
	public function __construct($str="now", DateTimeZone $timezone=null, $defaultDateFormat=null, $defaultTimeFormat=DateFormater::TIME_FORMAT_24){
		parent::__construct($str,$timezone);
		$this->_defaultTimeFormat=$defaultTimeFormat;
		$this->_defaultDateFormat=DateFormater::MYSQL_FORMAT;
		$this->_hold=false;
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->format($this->_defaultDateFormat);
	}

	/*--------------------------GETTERS--------------------*/
		/*----------------------DAYS-----------------------*/
	/**
	 *  Retourne le jour de la date courante
	 *
	 * @param bool $leading_zero Si true : retourne le jour (nombre) sinon retourne le jour (textuel)
	 *
	 * @return int|string
	 */
	public function getDay($leading_zero=false){
		if($leading_zero){
			return $this->format('d');
		}else{
			return intval($this->format('j'));
		}
	}

	/**
	 *  Retourne le jour de la semaine
	 *
	 * @param bool $str   Format literal (sinon numéro du jour de la semaine)
	 * @param bool $trunc Format tronqué
	 *
	 * @return string|int
	 */
	public function getDayOfWeek($str=false,$trunc=false){
		if($str){
			if($trunc){
				return $this->format('D');
			}else{
				return $this->format('l');
			}
		}else{
			return $this->format('w');
		}
	}

	/**
	 *  Retourne le numéro du jour dans l'année
	 * @return int
	 */
	public function getDayOfYear():int{
		return intval($this->format('z'));
	}

		/*----------------------WEEKS----------------------*/
	/**
	 *  Retourne le numéro de la semaine
	 * @return int
	 */
	public function getWeekNumber():int{
		return intval($this->format('W'));
	}

		/*----------------------MONTHS---------------------*/

	/**
	 *  Retourne le mois
	 * @param bool $str   Format littéral
	 * @param bool $trunc Tronqué
	 *
	 * @return int|string
	 */
	public function getMonth($str=false,$trunc=false){
		if($str){
			if($trunc){
				return $this->format('M');
			}else{
				return $this->format('F');
			}
		}else{
			if($trunc){
				return $this->format('m');
			}else{
				return intval($this->format('n'));
			}
		}
	}

	/**
	 *  Retourne le nombre de jours dans le mois
	 * @return int
	 */
	public function getDaysOfMonth():int{
		return intval($this->format('t'));
	}

		/*----------------------YEARS----------------------*/

	/**
	 *  Permet de savoir si l'année courante est bisextyle
	 * @return bool
	 */
	public function isLeap(){
		return (($this->format('L')===1)?true:false);
	}

	/**
	 *  Obtient l'année
	 *
	 * @param bool $trunc Tronquée
	 *
	 * @return int|string
	 */
	public function getYear($trunc=false){
		if($trunc){
			return $this->format('y');
		}else{
			return intval($this->format('Y'));
		}
	}

		/*----------------------TIME-----------------------*/

	/**
	 *  Retourne AM ou PM
	 *
	 * @param bool $uc Majuscule
	 *
	 * @return string
	 */
	public function getMeridiem($uc=false){
		if($uc){
			return $this->format('A');
		}else{
			return $this->format('a');
		}
	}

	/**
	 *  Retourne l'heure en fonction du format de l'objet.
	 *
	 * @param bool $leading_zero Compléte ou non avec un 0 devant les heures à un chiffre (1 -> 01)
	 *
	 * @return int|string
	 */
	public function getHours($leading_zero=false){
		if($this->_defaultTimeFormat===DateFormater::TIME_FORMAT_12){
			if($leading_zero){
				return $this->format('h');
			}else{
				return intval($this->format('g'));
			}
		}else{
			if($leading_zero){
				return $this->format('H');
			}else{
				return intval($this->format('G'));
			}
		}
	}

	/**
	 *  Retourne le nombre de minutes courante
	 *
	 * @param bool $leading_zero Ajoute un zéro devant les nombres à un seul chiffre
	 *
	 * @return int|string
	 */
	public function getMinutes($leading_zero=false){
		if($leading_zero){
			return $this->format('i');
		}else{
			return intval($this->format('i'));
		}
	}

	/**
	 *  Retourne le nombre de secondes courantes
	 *
	 * @param bool $leading_zero Ajoute un zéro devant les nombres à un seul chiffre
	 *
	 * @return int|string
	 */
	public function getSeconds($leading_zero=false){
		if($leading_zero==false){
			return $this->format('s');
		}else{
			return intval($this->format('s'));
		}
	}

	/**
	 *  Retourne la timezone courante
	 *
	 * @param bool $trunc Tronquée
	 *
	 * @return string
	 */
	public function getStringTimezone($trunc=false){
		if($trunc){
			return $this->format('T');
		}else{
			return $this->format('e');
		}
	}

	/**
	 *  Retourne la différence avec greenwich
	 *
	 * @param bool $colon Avec deux points pour séparation heure/minute
	 *
	 * @return string
	 */
	public function getGreenwichDiff($colon=false){
		if($colon){
			return $this->format('P');
		}else{
			return $this->format('O');
		}
	}

	/**
	 *  Retourne le décalage horaire en seconde
	 * @return int
	 */
	public function getTimezoneOffset():int{
		return $this->format('Z');
	}

	/**
	 *  Retourne la date courante au format iso 8601
	 * @return string
	 */
	public function getISO8601(){
		return $this->format('c');
	}

	/**
	 *  Retourne la date courante au format RFC 2822
	 * @return string
	 */
	public function getRFC2822(){
		return $this->format('r');
	}

	/**
	 *  Retourne un unix timestamp de la date courante
	 * @return int
	 */
	public function getUnixSeconds(){
		return intval($this->format('U'));
	}

	/*--------------------------SETTERS--------------------*/
	/**
	 *  Interdit/autorise les modifications sur la date courante
	 * @param bool $bool True autorise, false interdit
	 */
	public function hold(bool $bool){
		$this->_hold=filter_var($bool,FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 *  Permet de savoir si les modifications sont autorisées sur la date courante
	 * @return bool
	 */
	public function isHold(){
		return $this->_hold;
	}

	/**
	 *  Modifie le jour de la date courante
	 *
	 * @param int  $day   Nouveau jour
	 * @param bool $force Parr défaut la fonction est sécurisée. Si $force est à true, alors on autorise la création d'une date invalide
	 *
	 * @return $this
	 */
	public function setDay(int $day,$force=false){
		if($this->isHold()){
			return $this->copy()->setDay($day,$force);
		}
		if(checkdate($this->getMonth(),$this->getDay(),$this->getYear())){
			$t=$this->format("t");
			if($t<$day){
				if($force){
					$this->setDate($this->getYear(),$this->getMonth(),$t);
				}else{
					throw new UnexpectedValueException("Cannot set day number $day, ".$this->getMonth(true)." have only ".$this->getDaysOfMonth()." days !");
				}
			}else{
				$this->setDate($this->getYear(),$this->getMonth(),$day);
			}
			return $this;
		}else{
			throw new UnexpectedValueException("Invalid date !");
		}
	}

	/**
	 *  CHange le mois courant
	 *
	 * @param int $month Numéro du nouveau mois
	 *
	 * @return $this
	 */
	public function setMonth(int $month){
		if($this->isHold()){
			return $this->copy()->setMonth($month);
		}
		if(checkdate($this->getMonth(),$this->getDay(),$this->getYear())){
			if($month>0 && $month<13){
				$this->setDate($this->getYear(),$month,$this->getDay());
			}else{
				throw new UnexpectedValueException("Month have to be an integer between 1 and 12 both included.");
			}
			return $this;
		}else{
			throw new UnexpectedValueException("Invalid date !");
		}
	}

	/**
	 *  Change l'année courante
	 *
	 * @param int $year Nouvelle année
	 *
	 * @return $this
	 */
	public function setYear(int $year){
		if($this->isHold()){
			return $this->copy()->setYear($year);
		}
		if(checkdate($this->getMonth(),$this->getDay(),$this->getYear())){
			if($year >1969){
				$this->setDate($year,$this->getMonth(),$this->getDay());
			}else{
				throw new UnexpectedValueException("Cannot set years before 1970 !");
			}
			return $this;
		}else{
			throw new UnexpectedValueException("Invalid date !");
		}
	}

	/**
	 *  Change l'heure courante. Tiens compte du format courant
	 * @param int $h Nouvelle heure
	 *
	 * @return $this
	 */
	public function setHour(int $h){
		if($this->isHold()){
			return $this->copy()->setHour($h);
		}
		if($this->_defaultTimeFormat===DateFormater::TIME_FORMAT_12){
			if($h>0 && $h<13){
				$this->setTime($h,$this->getMinutes(),$this->getSeconds());
				return $this;
			}else{
				throw new OutOfRangeException("Time format is set to ".DateFormater::class."::TIME_FORMAT_12. 0<H<13 but $h given !");
			}
		}else{
			if($h>0 && $h<25){
				$this->setTime($h,$this->getMinutes(),$this->getSeconds());
				return $this;
			}else{
				throw new OutOfRangeException("Time format is set to ".DateFormater::class."::TIME_FORMAT_24. 0<H<25 but $h given !");
			}
		}
	}

	/**
	 *  Change les minutes courantes.
	 *
	 * @param int $m Nouvelles minutes
	 *
	 * @return $this
	 */
	public function setMinutes(int $m){
		if($this->isHold()){
			return $this->copy()->setMinutes($m);
		}
		if($m>0 && $m<60){
			$this->setTime($this->getHours(),$m,$this->getSeconds());
			return $this;
		}else{
			throw new OutOfRangeException("0<m<60 but $m given !");
		}
	}

	/**
	 *  Change les secondes courantes
	 *
	 * @param int $s Nouvelles secondes
	 *
	 * @return $this
	 */
	public function setSeconds(int $s){
		if($this->isHold()){
			return $this->copy()->setSeconds($s);
		}
		if($s && $s<60){
			$this->setTime($this->getHours(),$this->getMinutes(),$s);
			return $this;
		}else{
			throw new OutOfRangeException("0<s<60 but $s given !");
		}
	}

	/**
	 *  Passe au jour suivant
	 * @return Date
	 */
	public function nextDay(){
		return $this->addDays(1);
	}

	/**
	 *  Passe au jour précédent
	 * @return Date
	 */
	public function prevDay(){
		return $this->addDays(-1);
	}

	/**
	 *  Passe au mois suivant
	 * @return Date
	 */
	public function nextMonth(){
		return $this->addMonths(1);
	}

	/**
	 *  Passe au mois précédent
	 * @return Date
	 */
	public function prevMonth(){
		return $this->addMonths(-1);
	}

	/**
	 *  Passe à l'année suivante
	 * @return Date
	 */
	public function nextYear(){
		return $this->addYears(1);
	}

	/**
	 *  Passe à l'année précédente
	 * @return Date
	 */
	public function prevYear(){
		return $this->addYears(-1);
	}

	/*---------------------------METHODS-------------------------*/

	/**
	 *  Ajoute $n jours à la date courante
	 *
	 * @param int $n Nombre de jours
	 *
	 * @return $this
	 */
	public function addDays(int $n){
		if($this->isHold()){
			return $this->copy()->addDays($n);
		}
		$dt=new DateInterval("P".(($n<0)?$n*-1:$n)."D");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}

	/**
	 *  Ajoute $n semaines à la date courante
	 *
	 * @param int $n Nombre de semaines
	 *
	 * @return Date
	 */
	public function addWeeks(int $n){
		return $this->addDays(7*$n);
	}

	/**
	 *  Ajoute $n mois à la date courante
	 *
	 * @param int $n Nombre de mois
	 *
	 * @return $this
	 */
	public function addMonths(int $n){
		if($this->isHold()){
			return $this->copy()->addMonths($n);
		}
		$dt=new DateInterval("P".(($n<0)?$n*-1:$n)."M");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}

	/**
	 *  Ajoute $n années à la date courante
	 * @param int $n Nombre d'années
	 *
	 * @return $this
	 */
	public function addYears(int $n){
		if($this->isHold()){
			return $this->copy()->addYears($n);
		}
		$dt=new DateInterval("P".(($n<0)?$n*-1:$n)."Y");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}

	/**
	 *  Ajoute $n heures à la date courante
	 *
	 * @param int $n Nombre d'heures
	 *
	 * @return $this
	 */
	public function addHours(int $n){
		if($this->isHold()){
			return $this->copy()->addHours($n);
		}
		$dt=new DateInterval("P".(($n<0)?$n*-1:$n)."H");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}

	/**
	 *  Ajoute $n minutes à la date courante
	 * @param int $n Nombre de minutes
	 *
	 * @return $this
	 */
	public function addMinutes(int $n){
		if($this->isHold()){
			return $this->copy()->addMinutes($n);
		}
		$dt=new DateInterval("PT".(($n<0)?$n*-1:$n)."M");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}

	/**
	 *  AJoute $n secondes à la date courante
	 * @param int $n Nombre de secondes
	 *
	 * @return $this
	 */
	public function addSeconds(int $n){
		if($this->isHold()){
			return $this->copy()->addSeconds($n);
		}
		$dt=new DateInterval("P".(($n<0)?$n*-1:$n)."S");
		$dt->invert=(($n<0)?1:0);
		$this->add($dt);
		return $this;
	}


	/**
	 *  Retourne une copie de la date courante
	 * @return static
	 */
	public function copy(){
		$date=new static();
		$date->setDate($this->getYear(),$this->getMonth(),$this->getDay());
		$date->setTime($this->getHours(),$this->getMinutes(),$this->getSeconds());
		$date->setTimezone($this->getTimezone());
		return $date;
	}

	/**
	 *  Compare la date courante avec la date passée en paramètres.
	 *
	 * Retourne 1 si la date courante est antérieure à la date donnée
	 *        0 si elles sont égales
	 *        -1 si la date courante est postérieure à la date donnée
	 *
	 * @param $date Date à comparer
	 *
	 * @return int
	 */
	public function compare($date){
		if(!($date instanceof Date)){
			$date=new Date($date);
		}
		if($this->getUnixSeconds()<$date->getUnixSeconds()){
			return 1;
		}else if($this->getUnixSeconds()>$date->getUnixSeconds()){
			return -1;
		}else{
			return 0;
		}
	}
	/*------------------------------STATICS-------------------------------*/

	/**
	 *  Compare deux dates
	 * @param mixed $d1 Date 1
	 * @param mixed $d2 Date 2
	 *
	 * @return int
	 */
	public static function confront($d1,$d2){
		if(!($d1 instanceof Date)){
			$d1=new Date($d1);
		}
		return $d1->compare($d2);
	}
}

 