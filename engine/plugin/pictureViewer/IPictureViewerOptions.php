<?php
namespace wfw\engine\plugin\pictureViewer;

/**
 * Liste d'options d'un pictureViewer
 */
interface IPictureViewerOptions {
	/**
	 * @return bool True : fullscreen disponible
	 */
	public function hasFullscreen():bool;

	/**
	 * @return bool True : fléche de défilement disponible
	 */
	public function hasArrows():bool;

	/**
	 * @return bool True : bulle de progression disponible
	 */
	public function hasBullets():bool;

	/**
	 * @return bool True : une liste de photo horizontale apparait sous le slider
	 */
	public function hasTrail():bool;

	/**
	 * @return string Chemin d'accés au script permettant de synchroniser le trail avec la position
	 *                courante du slide
	 */
	public function trailScript():string;

	/**
	 * @return bool True : aperçu lors du survol des bulles de progression disponible
	 *              (nécessite hasBullets() : true
	 */
	public function hasBulletsPreview():bool;

	/**
	 * @return bool True : Le mode autoplay est activé.
	 */
	public function autoplayEnabled():bool;

	/**
	 * @return string Chemin relatif vers l'icone fleche gauche
	 */
	public function arrowLeftIcon():string;

	/**
	 * @return string Chemin relatif vers l'icone fleche droite
	 */
	public function arrowRightIcon():string;

	/**
	 * @return string Chemin relatif vers l'icone activer le mode plein écran
	 */
	public function fullscreenOnIcon():string;

	/**
	 * @return string Chemin relatif vers l'icone désactiver le mode plein écran
	 */
	public function fullscreenOffIcon():string;

	/**
	 * @return string Chemin relatif vers l'icone autoplay
	 */
	public function autoplayIcon():string;

	/**
	 * @return string Chemin d'accés au script permettant l'autoPlay
	 */
	public function autoPlayScript():string;

	/**
	 * @return string Chemin d'accés au css
	 */
	public function css():string;

	/**
	 * @return null|string Chemind d'accés au fichier de vue
	 */
	public function viewPath():?string;
}