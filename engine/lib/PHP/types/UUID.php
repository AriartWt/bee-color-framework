<?php
namespace wfw\engine\lib\PHP\types;

/**
 *  Représente un UUID
 */
class UUID {
	public const V4="v4";
	public const V6="v6";

	/** @var string $_uuid */
	private $_uuid;
	/** @var string $_version */
	private $_version;

	/**
	 * UUID constructor.
	 *
	 * @param string      $version (optionnel défaut : self::V6) Version à utiliser
	 * @param null|string $uuid    (optionnel) UUID
	 */
	public function __construct(string $version = self::V6,?string $uuid = null) {
		if(!is_null($uuid)){
			$this->_uuid = $uuid;
		}else{
			$this->_uuid = static::{$version}()->get();
		}
		$this->_version = $version;
	}

	/**
	 * @return string
	 */
	public function get():string{
		return $this->_uuid;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->get();
	}

	/**
	 *  Crée un UUID v4 en utilisant un script python
	 * @return UUID
	 */
	public static function v4():UUID{
		$data = random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
		$splited = str_split(bin2hex($data), 4);
		return new self(
			self::V4,
			$splited[0].$splited[1]
			."-".$splited[2]
			."-".$splited[3]
			."-".$splited[4]
			."-".$splited[5].$splited[6].$splited[7]);
	}

	/**
	 * Crée un UUID v6 (semi-sequentiel).
	 * @see
	 * @return UUID
	 */
	public static function v6(){
		// 0x01b21dd213814000 is the number of 100-ns intervals between the
		// UUID epoch 1582-10-15 00:00:00 and the Unix epoch 1970-01-01 00:00:00.
		$uuidTime = microtime(true)*10000000 + 0x01b21dd213814000;
		$uuidTime = (($uuidTime << 4) & 0xFFFFFFFFFFFF0000) | ($uuidTime & 0x0FFF) | 0x6000;
		$uuidTime = dechex($uuidTime);
		$res =  new self(
			self::V6,
			substr($uuidTime,0,8)
			."-".substr($uuidTime,8,4)
			."-".substr($uuidTime,12)
			."-".bin2hex(random_bytes(2))."-".bin2hex(random_bytes(6)));
		return $res;
	}
	/**
	 *  Transforme l'UUID courant en sa représentation hexadécimale
	 * @return string
	 */
	public function toHexString():string{
		$str = (new PHPString($this->_uuid))->replaceAll("-","");
		return $str;
	}

	/**
	 *  Restore un UUID depuis sa forme hexadecimale
	 *
	 * @param string $hex     Version hexadécimale
	 * @param string $version (optionnel défaut : v6) Version de l'UUID
	 *
	 * @return UUID
	 */
	public static function restoreDashes(string $hex,string $version=self::V6):UUID{
		$str = (new PHPString($hex))->insert([
			8 => "-",
			12 => "-",
			16 => "-",
			20 => "-"
		]);
		return new UUID($version,$str);
	}

	/**
	 *  Teste l'égalit de deux UUID. Tiens compte de la version
	 *
	 * @param UUID $uuid UUID à comparer
	 *
	 * @return bool
	 */
	public function equals(UUID $uuid){
		return $this->get() === $uuid->get() && $this->_version === $uuid->_version;
	}
}