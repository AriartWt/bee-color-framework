<?php
namespace wfw\engine\package\general\domain;

use wfw\engine\lib\data\string\json\IJSONPrintInfos;

/**
 * Adresse
 */
class Address implements IJSONPrintInfos{
	/** @var ZipCode $_zipCode */
	private $_zipCode;
	/** @var Street $_street */
	private $_street;
	/** @var Country $_country */
	private $_country;
	/** @var string $_complement */
	private $_complement;
	/** @var City $_city */
	private $_city;

	/**
	 * Address constructor.
	 * @param Street $street
	 * @param City $city
	 * @param ZipCode $zipCode
	 * @param Country $country
	 * @param string $complement
	 */
	public function __construct(
		Street $street,
		City $city,
		ZipCode $zipCode,
		Country $country,
		string $complement=''
	){
		$this->_zipCode = $zipCode;
		$this->_street = $street;
		$this->_country = $country;
		$this->_complement = $complement;
		$this->_city = $city;
	}

	/**
	 * @return ZipCode
	 */
	public function getZipCode(): ZipCode {
		return $this->_zipCode;
	}

	/**
	 * @return Street
	 */
	public function getStreet(): Street {
		return $this->_street;
	}

	/**
	 * @return Country
	 */
	public function getCountry(): Country {
		return $this->_country;
	}

	/**
	 * @return string
	 */
	public function getComplement(): string {
		return $this->_complement;
	}

	/**
	 * @return City
	 */
	public function getCity(): City {
		return $this->_city;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "$this->_street".(!empty($this->_complement)?'':"\n$this->_complement").
			"\n$this->_zipCode $this->_city $this->_country";
	}

	/**
	 * @return array string[](property names) : Liste des propriétés
	 *               à ne pas conserver.
	 */
	public function skipProperties(): array {
		return [];
	}

	/**
	 * @return array property => callable/value : Pour chaque objet, une liste de propriétés
	 *               dont chaque callable est une fonction qui prend pour argument la valeur de la propriété.
	 */
	public function transformProperties(): array {
		return [
			"_street" => (string) $this->_street,
			"_zipCode" => (string) $this->_zipCode,
			"_country" => (string) $this->_country,
			"_city" => (string) $this->_city
		];
	}

	/**
	 * @return array Propriété -> valeur/callable : Pour chaque propriété, un callable ou une valuer.
	 *               Si callable : accepte en argument l'objet lui même.
	 */
	public function addProperties(): array {
		return [];
	}
}