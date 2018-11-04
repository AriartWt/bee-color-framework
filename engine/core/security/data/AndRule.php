<?php
namespace wfw\engine\core\security\data;

/**
 * Effectue un ET logique avec les résultats des régles enregistrées.
 */
final class AndRule implements IRule {
	/** @var IRule[] $_rules */
	private $_rules;
	/** @var null|string $_message */
	private $_message;

	/**
	 * AndRule constructor.
	 *
	 * @param null|string $message  Message d'erreur en cas d'echec
	 * @param IRule[]     ...$rules Régle à appliquer
	 */
	public function __construct(?string $message=null,IRule ...$rules) {
		$this->_rules = $rules;
		$this->_message = $message;
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		$res = true;
		$errors = [];
		foreach($this->_rules as $rule){
			$report = $rule->applyTo($data);
			if(!$report->satisfied()){
				$errors[] = $report->errors();
				$res = false;
				//break;
			}
		}
		if($res){
			return new RuleReport(true);
		}else{
			return new RuleReport(
				false,
				array_merge_recursive(...$errors),
				$this->_message
			);
		}
	}
}