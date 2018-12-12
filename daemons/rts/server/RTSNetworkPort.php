<?php

namespace wfw\daemons\rts\server;

use wfw\daemons\rts\server\environment\IRTSEnvironment;
use wfw\daemons\rts\server\websocket\IWebsocketProtocol;
use wfw\engine\lib\network\socket\protocol\ISocketProtocol;

/**
 * Network port able to accept/read/write into websockets
 */
class RTSNetworkPort{
	/** @const int $max_client 1024 is the max with select(), we keep space for rejecting socket */
	protected const MAX_SOCKET_SELECT = 1000;
	/** @var resource $_mainSock */
	private $_mainSock;
	/** @var resource $_netSock */
	private $_netSock;
	/** @var IRTSEnvironment $_env */
	private $_env;
	/** @var array $_netSocks */
	private $_netSocks;
	/** @var ISocketProtocol $_mainProtocol */
	private $_mainProtocol;
	/** @var IWebsocketProtocol $_wsProtocol */
	private $_wsProtocol;

	// Configuration Start
	/** @var bool $debug verbose mode */
	private $_debug = false;
	/** @var int $mxRequestHandshakeSize chrome : ~503B firefox : ~530B IE 11 : ~297B */
	private $_maxRequestHandshakeSize = 1024;
	/** @var bool $headerOriginRequired */
	protected $_headerOriginRequired = false;
	/** @var bool $headerProtocolRequired */
	protected $_headerProtocolRequired = false;
	/** @var bool $willSupportExtensions */
	protected $_willSupportExtensions = false;
	// TODO : these 2 variables will be used to protect OOM and dynamically set max_client based on mem allowed per user
	protected $_maxWriteBuffer			  = 49152; //48K out
	protected $_maxReadBuffer			  = 49152; //48K in
	// Configuration End

	protected $userClass = 'WebSocketUser'; // redefine this if you want a custom user class.  The custom user class should inherit from WebSocketUser.
	protected $maxBufferSize;
	protected $master;
	protected $readWatchers                         = array();
	protected $writeWatchers                        = null;
	protected $users                                = array();
	protected $interactive                          = true;
	protected $nbclient                             = 0;
	protected $mem;

	/**
	 * RTSNetworkPort constructor.
	 *
	 * @param resource           $mainSocket Socket de communication avec le processus principal
	 * @param resource           $netSocket  Port network (reception des websockets)
	 * @param IRTSEnvironment    $env        Environnement RTS
	 * @param ISocketProtocol    $mainProtocol
	 * @param IWebsocketProtocol $wsProtocol
	 * @param bool               $debug
	 * @param int                $maxRequestHandshakeSize
	 */
	public function __construct(
		$mainSocket,
		$netSocket,
		IRTSEnvironment $env,
		ISocketProtocol $mainProtocol,
		IWebsocketProtocol $wsProtocol,
		bool $debug = false,
		int $maxRequestHandshakeSize = 1024
	) {
		$this->_mainSock = $mainSocket;
		$this->_netSock = $netSocket;
		$this->_env = $env;
		$this->_netSocks = [];
		$this->_debug = $debug;
		$this->_mainProtocol = $mainProtocol;
		$this->_wsProtocol = $wsProtocol;
	}

	public function start():void{
		while(true){
			//check main read/write
			//check net read/write
			//check all users
		}
	}

}