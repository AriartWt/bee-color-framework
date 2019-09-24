<?php
namespace wfw\daemons\modelSupervisor\server\environment;

/**
 *  Permet de gérer les components gréffés au ModelManagerServer
 */
interface IMSServerComponent {
	/**
	 *  Appelé par le ModuleInitializer
	 */
	public function start():void;

	/**
	 *  Appelé par le MSServer juste avant qu'il ne quitte, si la fonction haveToBeShutdownGracefully renvoie true
	 */
	public function shutdown():void;

	/**
	 * @return string Nom du composant
	 */
	public function getName():string;
}