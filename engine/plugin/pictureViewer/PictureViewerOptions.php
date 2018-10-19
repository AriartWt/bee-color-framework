<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/03/18
 * Time: 07:21
 */

namespace wfw\engine\plugin\pictureViewer;

/**
 * Options de base pour un picture viewer
 */
final class PictureViewerOptions implements IPictureViewerOptions
{
    /**
     * @var bool $_fullscreen
     */
    private $_fullscreen;
    /**
     * @var bool $_arrows
     */
    private $_arrows;
    /**
     * @var bool $_bullets
     */
    private $_bullets;
    /**
     * @var bool $_bulletsPreview
     */
    private $_bulletsPreview;
    /**
     * @var bool $_autoplay
     */
    private $_autoplay;

    /**
     * @var string $_arrowLeftIcon
     */
    private $_arrowLeftIcon;
    /**
     * @var string $_arrowRightIcon
     */
    private $_arrowRightIcon;
    /**
     * @var string $_autoplayIcon
     */
    private $_autoplayIcon;
    /**
     * @var string $_fullscreenOnIcon
     */
    private $_fullscreenOnIcon;
    /**
     * @var string $_fullscreenOffIcon
     */
    private $_fullscreenOffIcon;
    /**
     * @var string $_autoplayScript
     */
    private $_autoplayScript;
    /**
     * @var string $_css
     */
    private $_css;
    /**
     * @var null|string $_viewPath
     */
    private $_viewPath;

    /**
     * PictureViewerOptions constructor.
     *
     * @param bool[]      $options Liste des options
     * @param string[]    $icons   Liste des icones
     * @param null|string $css
     * @param null|string $autoplayJS
     * @param null|string $viewPath
     */
    public function __construct(
        array $options=[],
        array $icons=[],
        ?string $css=null,
        ?string $autoplayJS=null,
        ?string $viewPath=null)
    {
        (function(bool... $vals){})(...array_values($options));
        $this->_fullscreen = $options['fullscreen'] ?? true;
        $this->_arrows = $options['arrows'] ?? true;
        $this->_bulletsPreview = $options['bulletsPreview'] ?? true;
        $this->_bullets = $options['bullets'] ?? true;
        if($this->_bullets){
            if(isset($options['bulletsPreview'])){
                $this->_bulletsPreview = $options['bulletsPreview'];
            }else{
                $this->_bulletsPreview = true;
            }
        }else{
            $this->_bulletsPreview = false;
        }
        $this->_autoplay = $options['autoplay'] ?? true;
        (function(string... $vals){})(...array_values($icons));
        $this->_arrowLeftIcon = $icons['arrow_left'] ?? ENGINE.'/webroot/Image/svg/icons/left-arrow.svg';
        $this->_arrowRightIcon = $icons['arrow_right'] ?? ENGINE.'/webroot/Image/svg/icons/right-arrow.svg';
        $this->_fullscreenOnIcon = $icons['fullscreen_on'] ?? ENGINE.'/webroot/Image/svg/icons/expand.svg';
        $this->_fullscreenOffIcon = $icons['fullscreen_off'] ?? ENGINE.'/webroot/Image/svg/icons/collapse.svg';
        $this->_autoplayIcon = $icons['autoplay'] ?? ENGINE.'/webroot/Image/svg/icons/play1.svg';

        $this->_autoplayScript = $autoplayJS ?? '/website/JavaScript/plugins/pictureViewer/autoplay.js';
        $this->_css = $css ?? '/website/Css/plugins/pictureViewer/default.css';
        $this->_viewPath = $viewPath;
    }

    /**
     * @return bool True : fullscreen disponible
     */
    public function hasFullscreen(): bool
    {
        return $this->_fullscreen;
    }

    /**
     * @return bool True : fléche de défilement disponible
     */
    public function hasArrows(): bool
    {
        return $this->_arrows;
    }

    /**
     * @return bool True : bulle de progression disponible
     */
    public function hasBullets(): bool
    {
        return $this->_bullets;
    }

    /**
     * @return bool True : aperçu lors du survol des bulles de progression disponible
     *              (nécessite hasBullets() : true
     */
    public function hasBulletsPreview(): bool
    {
        return $this->_bulletsPreview;
    }

    /**
     * @return bool True : Le mode autoplay est activé.
     */
    public function autoplayEnabled(): bool
    {
        return $this->_autoplay;
    }

    /**
     * @return string
     */
    public function arrowLeftIcon(): string
    {
        return $this->_arrowLeftIcon;
    }

    /**
     * @return string
     */
    public function arrowRightIcon(): string
    {
        return $this->_arrowRightIcon;
    }

    /**
     * @return string
     */
    public function fullscreenOnIcon(): string
    {
        return $this->_fullscreenOnIcon;
    }
    /**
     * @return string
     */
    public function fullscreenOffIcon(): string
    {
        return $this->_fullscreenOffIcon;
    }

    /**
     * @return string
     */
    public function autoplayIcon(): string
    {
        return $this->_autoplayIcon;
    }

    /**
     * @return string Chemin d'accés au script permettant l'autoPlay
     */
    public function autoplayScript(): string
    {
        return $this->_autoplayScript;
    }

    /**
     * @return string Chemin d'accés au css
     */
    public function css(): string
    {
        return $this->_css;
    }

    /**
     * @return null|string Chemind d'accés au fichier de vue
     */
    public function viewPath(): ?string
    {
        return $this->_viewPath;
    }
}