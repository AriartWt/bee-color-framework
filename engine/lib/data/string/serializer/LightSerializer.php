<?php
namespace wfw\engine\lib\data\string\serializer;

use wfw\engine\lib\data\string\compressor\IStringCompressor;

/**
 * Serialise et deserialise des objets en eur appliquant une compression a la serialisation et
 * une décompression a désérialisation.
 */
class LightSerializer implements ISerializer {
	/** @var ISerializer $_serializer */
	private $_serializer;
	/** @var IStringCompressor $_compressor */
	private $_compressor;

	/**
	 * LightSerializer constructor.
	 *
	 * @param IStringCompressor $compressor Compresseur de chaine.
	 * @param null|ISerializer  $serializer (optionnel) Objet permettant la serialisation/deserialisation. Si null, la serialsiation php par défaut est utilisée.
	 */
	public function __construct(IStringCompressor $compressor,?ISerializer $serializer=null) {
		$this->_compressor = $compressor;
		$this->_serializer = $serializer??new PHPSerializer();
	}

	/**
	 * @param mixed $data Données à sérialiser.
	 *
	 * @return string Données sérialisées
	 */
	public function serialize($data): string {
		return $this->_compressor->compress($this->_serializer->serialize($data));
	}

	/**
	 * @param string $data Donnée à désérialiser.
	 *
	 * @return mixed Données désérialsiées.
	 */
	public function unserialize(string $data) {
		return $this->_serializer->unserialize($this->_compressor->decompress($data));
	}
}