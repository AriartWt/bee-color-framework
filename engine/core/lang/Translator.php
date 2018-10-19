<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/02/18
 * Time: 08:03
 */

namespace wfw\engine\core\lang;

/**
 * Permet d'effectuer des traductions, en se basant sur des StrRepositories créés à partir de
 * fichiers de langue.
 */
final class Translator implements ITranslator
{
    /**
     * @var IStrRepository[] $_repositories
     */
    private $_repositories;
    /**
     * @var string $_defaultLang
     */
    private $_defaultLang;
    /**
     * @var string $_baseKey
     */
    private $_baseKey;

    /**
     * Translator constructor.
     *
     * @param ILanguageLoader $loader Loader permettant de charger les fichiers de langue.
     * @param string[]        $langs Langues sous la forme "lang" => ["file_path1",...]
     * @param null|string     $defaultLang (optionnel) Langue par défaut.
     * @throws \InvalidArgumentException
     */
    public function __construct(ILanguageLoader $loader, array $langs, ?string $defaultLang=null)
    {
        if(empty($langs)){
            throw new \InvalidArgumentException("At least one lang have to be specified !");
        }
        $this->_repositories = [];
        foreach ($langs as $lang=>$paths){
            $this->_repositories[$lang] = $loader->load(...$paths);
        }
        $this->_defaultLang = $defaultLang ?? array_keys($this->_repositories)[0];
    }

    /**
     * @param string $key Clé d'obtention d'une chaine
     * @return string Chaine correspondante
     */
    public function get(string $key): string
    {
        return $this->_repositories[$this->_defaultLang]->get($this->_baseKey.$key);
    }

    /**
     * @param null|string $basePath Chemin de base ajouté devant les clén pour une résolution
     *                              relative. Null : resolution absolue.
     */
    public function changeBaseKey(?string $basePath = null): void
    {
        $this->_baseKey = $basePath ?? "";
    }

    /**
     * Obtient la chaine associée à $key et remplace un motif pré-établit par une occurence de
     * $replace, dans l'ordre dans lequel elles sont spécifiées.
     *
     * @param string   $key         Clé
     * @param string[] ...$replaces Remplacements
     * @return string Chaine correspondante, dont les motifs de remplacement sont substitués par les
     *                              termes fournis.
     */
    public function getAndReplace(string $key, string ...$replaces): string
    {
        return $this->_repositories[$this->_defaultLang]->getAndReplace(
            $this->_baseKey.$key,
            ...$replaces
        );
    }

    /**
     * @return string Langue par défaut.
     */
    public function getCurrentLanguage(): string
    {
        return $this->_defaultLang;
    }

    /**
     * @param string $lang Nouvelle langue par défaut.
     */
    public function changeCurrentLanguage(string $lang): void
    {
        if(isset($this->_repositories[$lang])){
            $this->_defaultLang = $lang;
        }else{
            throw new \InvalidArgumentException("Unknwown language $lang !");
        }
    }

    /**
     * @param string      $key  Clé d'obtention
     * @param null|string $lang Langue souhaitée
     * @return string
     */
    public function getAndTranslate(string $key, ?string $lang = null): string
    {
        $lang = $lang ?? $this->_defaultLang;
        return $this->_repositories[$lang]->get($this->_baseKey.$key);
    }

    /**
     * @param string      $key         Clé d'obtention.
     * @param null|string $lang        Langue souhaitée.
     * @param string[]    ...$replaces Liste des remplacements
     * @return string Chaine tranduite dont les remplacements ont été effectués.
     */
    public function getTranslateAndReplace(
        string $key,
        ?string $lang = null,
        string ...$replaces
    ): string
    {
        $lang = $lang ?? $this->_defaultLang;
        return $this->_repositories[$lang]->getAndReplace(
            $this->_baseKey.$key,
            ...$replaces
        );
    }

    /**
     * @param string $key Clé représentant à sous-ensemble de clés
     * @return null|\stdClass
     */
    public function getAll(string $key): ?\stdClass
    {
        return $this->_repositories[$this->_defaultLang]->getAll($key);
    }

    /**
     * @param string $key  Clé d'obtention
     * @param string $lang Langue souhaitée
     * @return \stdClass Ensemble de traductions
     */
    public function getAllTranslations(string $key, ?string $lang = null): \stdClass
    {
        $lang = $lang ?? $this->_defaultLang;
        return $this->_repositories[$lang]->getAll($key);
    }
}