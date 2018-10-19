<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/04/18
 * Time: 07:22
 */

namespace wfw\cli\backup\conf;

use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Configuration du gestionnaire de backups
 */
final class BackupManagerConf
{
    private const MANAGER_FOLDER = "manager_folder";
    private const BACKUP_FOLDER = "backup_folder";
    private const DATABASES = "databases";
    private const MAX_BACKUPS = "max_backups";
    private const MYSQLDUMP = "mysqldump";
    private const MYSQL = "mysql";

    /**
     * @var FileBasedConf $_conf
     */
    private $_conf;

    /**
     * @var string $_basepath
     */
    private $_basepath;

    /**
     * BackupManagerConf constructor.
     *
     * @param string      $engineConf Chemin d'accés aux configurations par defaut
     * @param null|string $siteConf   Chemin d'accés aux configurations du projet
     * @param string      $basepath   Chemin de base pour la résolution de chemins relatifs
     * @throws \InvalidArgumentException
     */
    public function __construct(string $engineConf,?string $siteConf, string $basepath = CLI)
    {
        $this->_basepath = $basepath;
        $confIo = new JSONConfIOAdapter();
        $conf = new FileBasedConf($engineConf,$confIo);
        if(file_exists($siteConf))
            $conf->merge(new FileBasedConf($siteConf,$confIo));

        $backupConf = $conf->get('server/cli/backup');
        if(is_null($backupConf))
            throw new \InvalidArgumentException("Config key server/cli/backup not found");
        if(!(new PHPString($backupConf))->startBy("/"))
            $backupConf = "$basepath/$backupConf";
        if(!file_exists($backupConf))
            throw new \InvalidArgumentException("Conf file $backupConf not found");
        $this->_conf = new FileBasedConf($backupConf,$confIo);
    }

    /**
     * @return string Repertoir où se trouve la serialisation du manager de backups précédent
     */
    public function getManagerFolder():string{
        $folder = $this->_conf->getString(self::MANAGER_FOLDER);
        if(!(new PHPString($folder))->startBy("/"))
            $folder = "$this->_basepath/$folder";
        return $folder;
    }

    /**
     * @return string Repertoire où se trouve les backups locaux
     */
    public function getBackupFolder():string{
        $folder = $this->_conf->getString(self::BACKUP_FOLDER);
        if(!(new PHPString($folder))->startBy("/"))
            $folder = "$this->_basepath/$folder";
        return $folder;
    }

    /**
     * @return int Nombre maximum de backups conservés
     */
    public function getMaxBackups():int{
        return $this->_conf->getInt(self::MAX_BACKUPS);
    }

    /**
     * @return array Liste des bases de données à sauvegarder
     */
    public function getDatabases():array{
        return $this->_conf->getArray(self::DATABASES);
    }

    /**
     * @return string Chemin d'accés à mysqldump
     */
    public function getMysqldumpPath():string{
        return $this->_conf->getString(self::MYSQLDUMP);
    }

    /**
     * @return string Chemin d'accés à mysql
     */
    public function getMysql():string{
        return $this->_conf->getString(self::MYSQL);
    }
}