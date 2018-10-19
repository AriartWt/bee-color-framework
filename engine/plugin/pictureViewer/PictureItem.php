<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/03/18
 * Time: 08:40
 */

namespace wfw\engine\plugin\pictureViewer;

/**
 * Picture item lisible par un PictureViewer
 */
final class PictureItem implements IPictureItem
{
    /**
     * @var string $_path
     */
    private $_path;
    /**
     * @var null|string $_title
     */
    private $_title;
    /**
     * @var null|string $_description
     */
    private $_description;
    /**
     * @var null|string $_alt
     */
    private $_alt;

    /**
     * PictureItem constructor.
     *
     * @param string      $path
     * @param null|string $title
     * @param null|string $description
     * @param null|string $alt
     */
    public function __construct(
        string $path,
        ?string $title=null,
        ?string $description=null,
        ?string $alt=null
    ){
        $this->_description = $description;
        $this->_path = $path;
        $this->_alt = $alt;
        $this->_title = $title;
    }

    /**
     * @return null|string Attribut alt
     */
    public function alt(): ?string
    {
        return $this->_alt;
    }

    /**
     * @return string Chemin complet vers l'image
     */
    public function path(): string
    {
        return $this->_path;
    }

    /**
     * @return null|string Titre associé
     */
    public function title(): ?string
    {
        return $this->_title;
    }

    /**
     * @return null|string Description
     */
    public function description(): ?string
    {
        return $this->_description;
    }
}