<?php
namespace wfw\engine\core\security\data\rules;

/**
 * Vérifie si les champs spécifiés sont des numéros de téléphone valides.
 */
final class IsPhoneNumber extends MatchRegexp {
	/**
	 * IsPhoneNumber constructor.
	 *
	 * @param string   $message   Message en cas d'erreur
	 * @param string ...$fields Champs à valider
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $message, string ...$fields) {
		parent::__construct(
			"/^(((\+|00)[0-9]{1,4}([ ])(\(0\)|))|0)[1-9]([\. -]|)([0-9]([\. -]{0,1})){8}$/",
			$message,
			...$fields
		);
	}
}