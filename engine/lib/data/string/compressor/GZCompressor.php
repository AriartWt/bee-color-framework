<?php
namespace wfw\engine\lib\data\string\compressor;

/**
 * Compresseur de chaines de cractères utilisants les fonctions gzdeflat et gzcompress
 */
class GZCompressor implements IStringCompressor {
	/** @var int $_level */
	private $_level;

	/**
	 * GZCompressor constructor.
	 *
	 * @param int $level Niveau de compression
	 */
	public function __construct(int $level=9) {
		$this->_level = $level;
	}

	/**
	 * Compresse la chaine passée en paramètre.
	 *
	 * @param string $string Données à compresser
	 *
	 * @return string Données compressées
	 */
	public function compress(string $string): string {
		return gzdeflate($string,$this->_level);
	}

	/**
	 * @param string $string Données à compresser
	 *
	 * @return string Données décompressées
	 */
	public function decompress(string $string): string {
	   return gzinflate($string);
	}
}