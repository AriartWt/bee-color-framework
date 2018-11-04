<?php
namespace wfw\engine\core\security\data;

/**
 * Implementationde base d'une regle
 */
abstract class ForEachFieldRule implements IRule {
	/** @var string[] $_fields */
	private $_fields;
	/** @var string $_message */
	private $_message;

	/**
	 * AbstractRule constructor.
	 *
	 * @param string $message Message en cas d'erreur
	 * @param string ...$fields Champs
	 */
	public function __construct(string $message, string ...$fields) {
		$this->_fields = $fields;
		$this->_message = $message;
	}

	/**
	 * @return array Liste des champs auxquels appliquer la régles dans le tableau de données
	 */
	protected final function fields():array{
		return $this->_fields;
	}

	 /**
	  * @param array $data Données auxquelles appliquer la règle.
	  * @return IRuleReport
	  */
	 public function applyTo(array $data): IRuleReport {
		 $errors = [];
		 foreach($this->_fields as $field){
			 if(!$this->applyOn($data[$field]??null)){
				$errors[$field] = $this->_message;
			 }
		 }
		 if(count($errors) > 0){
			 return new RuleReport(false,$errors,$this->_message);
		 }else{
			 return new RuleReport(true);
		 }
	 }

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	 protected abstract function applyOn($data):bool;

	/**
	 * @param string $message Nouveau message
	 */
	 protected final function changeMessage(string $message):void{
		$this->_message = $message;
	 }
 }