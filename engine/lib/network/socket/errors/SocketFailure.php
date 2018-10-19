<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/08/18
 * Time: 18:08
 */

namespace wfw\engine\lib\network\socket\errors;
use Throwable;

/**
 * Survient lors d'une erreur de manipulation sur une socket.
 */
final class SocketFailure extends \Exception{
	public const NOTSOCK =        88;  /* Socket operation on non-socket */
	public const DESTADDRREQ =    89;  /* Destination address required */
	public const MSGSIZE =        90;  /* Message too long */
	public const PROTOTYPE =      91;  /* Protocol wrong type for socket */
	public const NOPROTOOPT =     92;  /* Protocol not available */
	public const PROTONOSUPPORT = 93;  /* Protocol not supported */
	public const SOCKTNOSUPPORT = 94;  /* Socket type not supported */
	public const OPNOTSUPP =    95;    /* Operation not supported on transport ndpoint */
	public const PFNOSUPPORT =  96;    /* Protocol family not supported */
	public const AFNOSUPPORT =  97;    /* Address family not supported by protocol */
	public const ADDRINUSE =    98;    /* Address already in use */
	public const ADDRNOTAVAIL = 99;    /* Cannot assign requested address */
	public const NETDOWN =      100;   /* Network is down */
	public const NETUNREACH =   101;   /* Network is unreachable */
	public const NETRESET =     102;   /* Network dropped connection because of reset */
	public const CONNABORTED =  103;   /* Software caused connection abort */
	public const CONNRESET =    104;   /* Connection reset by peer */
	public const NOBUFS =       105;   /* No buffer space available */
	public const ISCONN =       106;   /* Transport endpoint is already connected */
	public const NOTCONN =      107;   /* Transport endpoint is not connected */
	public const SHUTDOWN =     108;   /* Cannot send after transport endpoint shutdown */
	public const TOOMANYREFS =  109;   /* Too many references: cannot splice */
	public const TIMEDOUT =     110;   /* Connection timed out */
	public const CONNREFUSED =  111;   /* Connection refused */
	public const HOSTDOWN =     112;   /* Host is down */
	public const HOSTUNREACH =  113;   /* No route to host */
	public const ALREADY =      114;   /* Operation already in progress */
	public const INPROGRESS =   115;   /* Operation now in progress */
	public const REMOTEIO =     121;   /* Remote I/O error */
	public const CANCELED =     125;   /* Operation Canceled */

	/** @var int $_errNo */
	private $_errNo;

	/**
	 * SocketFailure constructor.
	 *
	 * @param null|string    $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct(?string $message = null, int $code = 0, Throwable $previous = null) {
		parent::__construct($message??socket_strerror($code), $code, $previous);
		$this->_errNo = $code;
	}

	/**
	 * @return int
	 */
	public function getSocketErrCode():int{
		return $this->_errNo;
	}
}