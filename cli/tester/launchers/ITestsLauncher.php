<?php
namespace wfw\cli\tester\launchers;

/**
 * Permet de lancer une suite de tests.
 */
interface ITestsLauncher {
	/**
	 * Lance les tests
	 *
	 * @param string[] $tests Liste de tests à lancer
	 */
	public function launchTests(string... $tests):void;

	/**
	 * Lance les tests
	 *
	 * @param string[] $sequences Liste de séquences à lancer
	 */
	public function launchSequences(string... $sequences):void;
}