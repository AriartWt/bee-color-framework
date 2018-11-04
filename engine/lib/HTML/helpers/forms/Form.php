<?php
namespace wfw\engine\lib\HTML\helpers\forms;

use InvalidArgumentException;
use wfw\engine\core\security\data\IRule;
use wfw\engine\lib\HTML\helpers\forms\errors\InvalidFormKey;
use wfw\engine\lib\HTML\helpers\forms\inputs\Checkbox;
use wfw\engine\lib\HTML\helpers\forms\inputs\ErrorInput;
use wfw\engine\lib\HTML\helpers\forms\validation\IValidationPolicy;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Formulaire HTML. Permet l'ajout de Honeypot pour la protection contre les spams
 */
class Form implements IHTMLForm {
	/** @var UUID $_key */
	private $_key;
	/** @var IHTMLInput[] $_inputs */
	private $_inputs;
	/** @var IRule $_rule */
	private $_rule;
	/** @var array $_errors */
	private $_errors;
	/** @var string $_errorIcon */
	private $_errorIcon;
	/** @var IValidationPolicy $_policy */
	private $_policy;

	/**
	 * Form constructor.
	 *
	 * @param IRule             $rule      Régle de validation des données du formulaire
	 * @param string            $errorIcon Chemin d'accés à l'icone d'erreur
	 * @param IValidationPolicy $policy
	 */
	public function __construct(IRule $rule,string $errorIcon,IValidationPolicy $policy){
		$this->_key = new UUID(UUID::V4);
		$this->_rule = $rule;
		$this->_inputs = [];
		$this->_errorIcon = $errorIcon;
		$this->_policy = $policy;
	}

	/**
	 * @return string
	 */
	public function getKey():string{
		return (string) $this->_key;
	}

	/**
	 * @param string $key Clé à tester
	 * @return bool
	 */
	public function matchKey(string $key):bool{
		return ((string)$this->_key) === $key;
	}

	/**
	 * @param IHTMLInput $input Input à ajouter
	 */
	public function addInput(IHTMLInput $input): void {
		$this->_inputs[$input->getName()] = $input;
	}

	/**
	 * @param IHTMLInput ...$inputs
	 */
	public function addInputs(IHTMLInput ...$inputs): void {
		foreach ($inputs as $input){ $this->addInput($input); };
	}

	/**
	 * @param string $name Nom de l'input à récupérer
	 * @return IHTMLInput
	 */
	public function get(string $name): IHTMLInput {
		if(!isset($this->_inputs[$name])) throw new InvalidArgumentException("Unknows field $name");
		$res = null;
		if(isset($this->_errors[$name]))
			$res = new ErrorInput(
				$this->_inputs[$name],
				$this->_errorIcon,
				is_array($this->_errors[$name])
					? implode("\n",$this->_errors[$name])
					: $this->_errors[$name]
			);
		return $res ?? $this->_inputs[$name];
	}

	/**
	 * @param array       $data Données à valider
	 * @param null|string $key  (optionnel) Clé du formulaire
	 * @return bool True si le formulaire rempli par l'utilisateur est conforme, false sinon
	 */
	public function validates(array $data,?string $key=null): bool {
		if(!is_null($key) && !$this->matchKey($key)) throw new InvalidFormKey(
			"Wrong form key given !"
		);

		$report = $this->_rule->applyTo($data);
		if(!$report->satisfied()){
			$this->_errors = $report->errors();
		}
		$passed=[];
		foreach($data as $k=>$v){
			if(isset($this->_inputs[$k])){
				$this->_inputs[$k]->setData($v);
				$passed[]=$k;
			}
		}
		foreach(array_diff(array_keys($this->_inputs),$passed) as $k){
			if($this->_inputs[$k] instanceof Checkbox) $this->_inputs[$k]->setData(false);
		}

		return $report->satisfied() && $this->_policy->apply($data);
	}

	/**
	 * @return bool
	 */
	public function hasErrors(): bool {
		return is_array($this->_errors) && count($this->_errors) > 0;
	}
}