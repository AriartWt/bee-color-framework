<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/09/18
 * Time: 15:45
 */

namespace wfw\engine\lib\HTML\helpers\forms\validation;

use wfw\engine\lib\HTML\helpers\forms\errors\TooShortSubmissionTime;

/**
 * Rejette le formulaire s'il a été soumis avant un certain temps (soumission trop rapide)
 */
final class MinTimeValidity implements IValidationPolicy{
	/** @var mixed $_date */
	private $_date;
	/** @var float $_time */
	private $_time;

	/**
	 * MinTimeValidityPolicy constructor.
	 * @param float $time Temps minimum avant soumission en secondes (default : 5s)
	 */
	public function __construct(float $time=5) {
		$this->_date = microtime(true);
		$this->_time = $time;
	}

	/**
	 * Si la politique est verifiée, renvoie true, sinon il est préférable de lever une
	 * exception.
	 *
	 * @param array $data Données à valider
	 * @return bool
	 */
	public function apply(array &$data): bool {
		$submissionTime = (microtime(true) - $this->_date);
		if($submissionTime < $this->_time) throw new TooShortSubmissionTime(
			"The user submited this form in $submissionTime seconds. "
			."The sumbission source can be a spammer, a spambot or a malicious attacker"
		);
		return true;
	}
}