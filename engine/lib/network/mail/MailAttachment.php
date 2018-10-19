<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/06/18
 * Time: 16:47
 */

namespace wfw\engine\lib\network\mail;

/**
 * Pice jointe
 */
final class MailAttachment implements IMailAttachment{
	/** @var string $_path */
	private $_path;
	/** @var null|string $_name */
	private $_name;
	/** @var null|string $_encoding */
	private $_encoding;
	/** @var null|string $_type */
	private $_type;
	/** @var null|string $_disposition */
	private $_disposition;
	
	/**
	 * MailAttachment constructor.
	 *
	 * @param string      $path
	 * @param null|string $name
	 * @param null|string $encoding
	 * @param null|string $type
	 * @param null|string $disposition
	 */
	public function __construct(
		string $path,
		?string $name = null,
		?string $encoding = null,
		?string $type = null,
		?string $disposition = null
	){
		$this->_path = $path;
		$this->_name = $name;
		$this->_encoding = $encoding;
		$this->_type = $type;
		$this->_disposition = $disposition;
	}
	
	/**
	 * @return string
	 */
	public function path(): string {
		return $this->_path;
	}
	
	/**
	 * @return null|string
	 */
	public function name(): ?string {
		return $this->_name;
	}
	
	/**
	 * @return null|string
	 */
	public function encoding(): ?string {
		return $this->_encoding;
	}
	
	/**
	 * @return null|string
	 */
	public function disposition(): ?string {
		return $this->_disposition;
	}
	
	/**
	 * @return null|string
	 */
	public function type(): ?string {
		return $this->_type;
	}
}