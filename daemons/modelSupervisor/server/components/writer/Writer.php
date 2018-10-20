<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/01/18
 * Time: 07:24
 */

namespace wfw\daemons\modelSupervisor\server\components\writer;

use wfw\daemons\kvstore\server\KVSModes;
use wfw\daemons\modelSupervisor\server\components\writer\modelManager\ModelManager;
use wfw\daemons\modelSupervisor\server\components\writer\params\worker\WriterComponentWorkerParams;
use wfw\daemons\modelSupervisor\server\components\writer\requestHandler\WriterRequestHandler;
use wfw\daemons\modelSupervisor\server\components\writer\requests\IWriterRequest;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponentEnvironment;
use wfw\daemons\modelSupervisor\server\environment\IMSServerComponent;
use wfw\daemons\modelSupervisor\server\requestHandler\IMSServerRequestHandlerManager;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;

use wfw\engine\core\data\DBAccess\NOSQLDB\kvs\KVSAccess;
use wfw\engine\core\data\DBAccess\SQLDB\MySQLDBAccess;
use wfw\engine\core\data\model\builder\GenericModelBuilder;
use wfw\engine\core\data\model\loaders\KVStoreBasedModelLoader;
use wfw\engine\core\data\model\snapshoter\ModelSnapshoter;
use wfw\engine\core\data\model\storage\KVSBasedModelStorage;
use wfw\engine\core\data\model\synchronizer\IModelSynchronizer;
use wfw\engine\core\data\model\synchronizer\ModelSynchronizer;
use wfw\engine\lib\data\string\serializer\ISerializer;
use wfw\engine\lib\network\socket\data\IDataParser;
use wfw\engine\lib\PHP\types\PHPString;

/**
 *  Composant gérant les écritures et modifications des models.
 */
final class Writer implements IMSServerComponent
{
    public const NAME = "writer";

    /**
     * @var WriterWorker $_worker
     */
    private $_worker;

    /**
     * @var string $_serverkey
     */
    private $_serverkey;

    /**
     *  Appelé par le MSServerModuleInitializer
     *
     * @param string                                 $socket_path
     * @param string                                 $serverKey
     * @param array                                  $modelList
     * @param ISerializer                    $serializer
     * @param IDataParser                    $dataParser
     * @param IMSServerComponentEnvironment  $environment
     * @param IMSServerRequestHandlerManager $requestHandlerManager
     *
     * @throws \wfw\daemons\kvstore\client\errors\AlreadyLogged
     * @throws \wfw\daemons\kvstore\client\errors\KVSClientFailure
     * @throws \wfw\daemons\kvstore\errors\KVSFailure
     */
    public function __construct(
        string $socket_path,
        string $serverKey,
        array $modelList,
        ISerializer $serializer,
        IDataParser $dataParser,
        IMSServerComponentEnvironment $environment,
        IMSServerRequestHandlerManager $requestHandlerManager
    )
    {
        $this->_serverkey = $serverKey;
        //On initialise le mode de stockage par défaut pour les KVSAccess
        $defaultStorage = $environment->getString("kvs/default_storage");
        if(!is_null($defaultStorage) && KVSModes::exists($defaultStorage)){
            $defaultStorage = KVSModes::get($defaultStorage);
        }else{
            $defaultStorage = null;
        }

        //Acces pour le ModelLoader
        $kvsAccessLoader = new KVSAccess(
            $environment->getString("kvs/addr"),
            $environment->getString("kvs/login"),
            $environment->getString("kvs/password"),
            $environment->getString("kvs/container"),
            $defaultStorage
        );
        $kvsAccessLoader->login();

        //Access pour le ModelStorage
        $kvsAccessStorage = new KVSAccess(
            $environment->getString("kvs/addr"),
            $environment->getString("kvs/login"),
            $environment->getString("kvs/password"),
            $environment->getString("kvs/container"),
            $defaultStorage
        );
        $kvsAccessStorage->login();

        //Si le serveur n'a pas été éteint correctement et qu'un worker est toujours actif,
        //on le tue.
        $pidFile = $environment->getWorkingDir().DS."pid";
        if(file_exists($pidFile)){
            posix_kill(file_get_contents($pidFile),9);
            unlink($pidFile);
        }

        $synchronizer = $this->createSynchronizer(
            $environment,
            $serializer,
            $modelList
        );
        $synchronizer->synchronize();

        $this->_worker = new WriterWorker(
            $socket_path,
            [
                "protocol" => new MSServerSocketProtocol()
            ],
            dirname($socket_path),
            $serializer,
            $dataParser,
            $environment,
            new WriterComponentWorkerParams(
                $serverKey,
                new ModelManager(
                    new KVStoreBasedModelLoader(
                        $kvsAccessLoader,
                        $modelList
                    ),
                    new KVSBasedModelStorage(
                        $kvsAccessStorage
                    )
                ),
                $synchronizer
            )
        );

        $requestHandlerManager->addRequestHandler(
            IWriterRequest::class,
            new WriterRequestHandler($this->_worker));
    }

    /**
     * Crée un modelSyncrhonizer
     *
     * @param IMSServerComponentEnvironment $environment
     * @param ISerializer                   $serializer
     * @param array                         $modelList
     * @return IModelSynchronizer
     * @throws \InvalidArgumentException
     */
    private function createSynchronizer(
        IMSServerComponentEnvironment $environment,
        ISerializer $serializer,
        array $modelList
    ):IModelSynchronizer
    {
        $snapshotDir = $environment->getString("snapshot_path")??$environment->getWorkingDir();
        $snapshotDir = new PHPString($snapshotDir);
        if(!$snapshotDir->startBy("/")){
            $snapshotDir = $environment->getWorkingDir().DS.$snapshotDir;
        }
        $snapshotDir = (string)$snapshotDir;
        //On initialise le mode de stockage par défaut pour les KVSAccess
        $defaultStorage = $environment->getString("kvs/default_storage");
        if(!is_null($defaultStorage) && KVSModes::exists($defaultStorage)){
            $defaultStorage = KVSModes::get($defaultStorage);
        }else{
            $defaultStorage = null;
        }

        $kvsAccess = new KVSAccess(
            $environment->getString("kvs/addr"),
            $environment->getString("kvs/login"),
            $environment->getString("kvs/password"),
            $environment->getString("kvs/container"),
            $defaultStorage
        );
        $modelStorage = new KVSBasedModelStorage($kvsAccess);

        $snapshoter = new ModelSnapshoter(
            new MySQLDBAccess(
                $environment->getString("mysql/host"),
                $environment->getString("mysql/database"),
                $environment->getString("mysql/login"),
                $environment->getString("mysql/password")
            ),
            $snapshotDir,
            $modelList,
            new GenericModelBuilder(),
            $serializer
        );
        $synchronizer = new ModelSynchronizer($modelStorage,$snapshoter);
        return $synchronizer;
    }

    /**
     *  Appelé par le MSServer
     */
    public function start(): void
    {
        $this->_worker->start();
    }

    /**
     *  Appelé par le ModelManagerServer juste avant qu'il ne quitte, si la fonction haveToBeShutdownGracefully renvoie true
     */
    public function shutdown(): void
    {
        $this->_worker->shutdown($this->_serverkey);
    }

    /**
     * @return string Nom du composant
     */
    public function getName(): string
    {
        return self::NAME;
    }
}