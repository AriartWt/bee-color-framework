<?php
namespace wfw\cli\tester\launchers;

/**
 * Sequence de tests
 */
interface ITestSequence {
	/**
	 * Permet de lancer la séquence de tests
	 */
	public function start():void;
}