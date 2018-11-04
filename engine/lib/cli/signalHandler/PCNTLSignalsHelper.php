<?php
namespace wfw\engine\lib\cli\signalHandler;

/**
 *  Classe helper pour l'utilisation de l'extension pcntl
 */
class PCNTLSignalsHelper {
	public const SIGHUP = SIGHUP;
	public const SIGINT = SIGINT;
	public const SIGQUIT = SIGQUIT;
	public const SIGILL = SIGILL;
	public const SIGTRAP = SIGTRAP;
	public const SIGABRT = SIGABRT;
	public const SIGIOT = SIGIOT;
	public const SIGBUS = SIGBUS;
	public const SIGFPE = SIGFPE;
	public const SIGKILL = SIGKILL;
	public const SIGUSR1 = SIGUSR1;
	public const SIGSEGV = SIGSEGV;
	public const SIGUSR2 = SIGUSR2;
	public const SIGPIPE = SIGPIPE;
	public const SIGALRM = SIGALRM;
	public const SIGTERM = SIGTERM;
	public const SIGSTKFLT = SIGSTKFLT;
	public const SIGCLD = SIGCLD;
	public const SIGCHLD = SIGCHLD;
	public const SIGCONT = SIGCONT;
	public const SIGSTOP = SIGSTOP;
	public const SIGTSTP = SIGTSTP;
	public const SIGTTIN = SIGTTIN;
	public const SIGTTOU = SIGTTOU;
	public const SIGURG = SIGURG;
	public const SIGXCPU = SIGXCPU;
	public const SIGXFSZ = SIGXFSZ;
	public const SIGVTALRM= SIGVTALRM;
	public const SIGPROF = SIGPROF;
	public const SIGWINCH = SIGWINCH;
	public const SIGPOLL= SIGPOLL;
	public const SIGIO = SIGIO;
	public const SIGPWR = SIGPWR;
	public const SIGSYS = SIGSYS;
	public const SIGBABY = SIGBABY;

	/**
	 * PCNTLSignalsHelper constructor.
	 *
	 * @param bool|null $async_signals Si true: active les signaux asynchornes. Si false, désactive les signaux. Si null : garde la valeur par défaut.
	 */
	public function __construct(?bool $async_signals = true)
	{
		if($async_signals){
			pcntl_async_signals(true);
		}else if(!is_null($async_signals)){
			pcntl_async_signals(false);
		}
	}

	/**
	 *  Enregistre un handler pour un signal
	 * @param int      $signo            Singal
	 * @param callable $callable         Intercepteur
	 * @param bool     $restart_sys_call Le paramètre optionnel restart_syscalls spécifie si l'appel système de redémarrage (restarting) doit être utilisé lorsque ce signal arrive.
	 */
	public function handle(int $signo,callable $callable,bool $restart_sys_call=true){
		pcntl_signal($signo,$callable,$restart_sys_call);
	}

	/**
	 * @param int[]    $signos
	 * @param callable $callable
	 * @param bool     $restart_sys_call
	 */
	public function handleAll(array $signos,callable $callable,bool $restart_sys_call=true){
		foreach($signos as $signo){
			$this->handle($signo,$callable,$restart_sys_call);
		}
	}

	/**
	 * @param array $without (optionnel) Signaux à exclure.
	 *
	 * @return array
	 */
	public static function getSignals(array $without=[]):array{
		return array_diff((new \ReflectionClass(self::class))->getConstants(),$without);
	}
}