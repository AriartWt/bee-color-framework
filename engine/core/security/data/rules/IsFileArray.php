<?php

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Class IsFileArray
 *
 * @package wfw\engine\core\security\data\rules
 */
final class IsFileArray extends ForEachFieldRule {
	/** @var IsFile $_rule */
	private $_rule;

	/**
	 * IsFileArray constructor.
	 *
	 * @param string $message       Failure message
	 * @param int    $maxSize       Max file size
	 * @param int    $totalMaxSize  Total max size of all files
	 * @param array  $acceptedMimes Accepted mime types
	 * @param string ...$fields     List of fields
	 */
	public function __construct(
		string $message,
		int $maxSize = -1,
		int $totalMaxSize = -1,
		$acceptedMimes = [],
		string ...$fields
	) {
		parent::__construct($message, ...$fields);
		$this->_rule = new IsFile('',$maxSize,$acceptedMimes,'file');
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(!is_array($data)) return false;
		$files = $this->extractFiles($data);
		foreach($files as $file){
			if(!$this->_rule->applyTo(["file"=>$file])->satisfied()) return false;
		}
		return true;
	}

	/**
	 * @param $data
	 * @return array
	 */
	private function extractFiles($data):array{
		$keys = ["name","type","tmp_name","error"];
		if(count(array_diff_key(array_flip($keys),$data)) === 0){
			$res = [];
			foreach($data as $k=>$values){
				foreach($values as $i=>$value){
					if(!isset($res[$i])) $res[$i] = [];
					$res[$i][$k] = $value;
				}
			}
			return $res;
		}else return $data;
	}
}