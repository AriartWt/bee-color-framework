<?php

namespace wfw\daemons\rts\server\app;

use wfw\daemons\rts\server\app\events\errors\EmptyMessageReceived;
use wfw\daemons\rts\server\app\events\errors\InvalidMessageReceived;
use wfw\daemons\rts\server\app\events\errors\NoHandlerDefined;
use wfw\daemons\rts\server\app\events\IRTSAppEvent;
use wfw\daemons\rts\server\app\events\IRTSAppEventListener;
use wfw\daemons\rts\server\app\events\IRTSAppEventSubscriber;
use wfw\daemons\rts\server\app\events\RTSAppEventEmitter;
use wfw\engine\lib\PHP\types\UUID;

/**
 * Base rts class
 */
abstract class RTSApp implements IRTSApp{
	/** @var callable[][] $_messageListeners */
	private $_messageListeners;
	/** @var callable[][] $_eventListeners */
	private $_eventListeners;
	/** @var string $_id */
	private $_id;
	/** @var RTSAppEventEmitter $_emitter */
	private $_emitter;
	/** @var string $_appName */
	private $_appName;
	/** @var int $_currentScope */
	private $_currentScope;

	/**
	 * RTSApp constructor.
	 *
	 * @param RTSAppEventEmitter $emitter
	 * @param string             $appName
	 * @param int                $currentScope
	 */
	public function __construct(
		RTSAppEventEmitter $emitter,
		int $currentScope,
		string $appName = '*'
	){
		$this->_currentScope = $currentScope;
		$this->_appName = $appName;
		$this->_emitter = $emitter;
		$this->_messageListeners = [];
		$this->_id = (string) new UUID(UUID::V4);
	}

	/**
	 * Return the app key that will be used on the handshake to check if an app can recieve events.
	 * Use the special key * to accept all connections on the same app.
	 *
	 * @return string The app key
	 */
	public final function getKey(): string {
		return $this->_appName;
	}

	/**
	 * @return int
	 */
	public function getCurrentScope(): int {
		return $this->_currentScope;
	}

	/**
	 * @return string
	 */
	public final function getId(): string {
		return $this->_id;
	}

	/**
	 * Produce events that must be emitted through the dispatch method.
	 * Event production must not mutate the current App state !
	 * All produced events that muste be processed by the current instance will be applyed through
	 * the applyRTSEvents method later.
	 * @param string $data data received through websocket
	 */
	public final function receiveData(string $data): void {
		if(!empty($data)){
			$d = json_decode($data,true);
			if(json_last_error() === JSON_ERROR_NONE){
				if(isset($d["event"])){
					if($this->_messageListeners[$d["event"]]){
						foreach($this->_messageListeners[$d["event"]] as $callable){
							$callable($this,$d["data"]??null);
						}
					}else throw new NoHandlerDefined("No handler defined to handle '{$d["event"]}'");
				}else throw new InvalidMessageReceived("An event field must be defined !");
			}else throw new InvalidMessageReceived(
				"(".json_last_error().") Unable to decode message $data : ".json_last_error_msg()
			);
		}else throw new EmptyMessageReceived("Empty messages not allowed.");
	}

	/**
	 * @param IRTSAppMessageSubscriber ...$subscribers
	 */
	public final function subscribeToAppMessage(IRTSAppMessageSubscriber ...$subscribers):void{
		foreach($subscribers as $subscriber){
			foreach($subscriber->getEvents() as $event=>$method){
				if(method_exists($subscriber,$method)) $this->onMessage(
					$event,[$subscriber,$method]
				);
				else throw new \InvalidArgumentException(
					"Error in message subscriber ".get_class($subscriber)." : method $method not found !"
				);
			}
		}
	}

	/**
	 * @param string   $event Event to listen
	 * @param callable $callable
	 */
	public final function onMessage(string $event,callable $callable):void{
		if(!isset($this->_messageListeners[$event])) $this->_messageListeners[$event] = [];
		$this->_messageListeners[$event][] = $callable;
	}

	/**
	 * Dispatch events
	 * @param IRTSAppEvent ...$event
	 */
	public final function dispatch(IRTSAppEvent... $event):void{
		$this->_emitter->dispatch(...$event);
	}

	/**
	 * @param IRTSAppEventSubscriber ...$subscribers
	 * @throws \InvalidArgumentException
	 */
	public final function subscribeToAppEvents(IRTSAppEventSubscriber ...$subscribers):void{
		foreach($subscribers as $subscriber){
			foreach($subscriber->getEvents() as $class => $method){
				if(method_exists($subscriber,$method)) $this->onEvent(
					$class,[$subscriber,$method]
				);
				else throw new \InvalidArgumentException(
					"Error in event subscriber ".get_class($subscriber)." : method $method not found !"
				);
			}
		}
	}

	/**
	 * @param string   $class
	 * @param callable $callable
	 */
	public final function onEvent(string $class, callable $callable):void{
		if(!isset($this->_eventListeners[$class])) $this->_eventListeners[$class] = [];
		$this->_eventListeners[$class][] = $callable;
	}

	/**
	 * THose events can or not mutate the current state or produce some side effects/events.
	 * @param IRTSAppEvent[] $events Events to apply to the current instance
	 */
	public final function applyRTSEvents(IRTSAppEvent ...$events) {
		foreach($events as $event){
			foreach($this->_eventListeners as $class=>$callables){
				if(is_a($event,$class)){
					foreach($callables as $callable){
						$callable($this,$event);
					}
				}
			}
		}
	}

	 /**
	  * Add a listener for the events. Listeners will recieve events when they're created.
	  *
	  * @param string               $eventClass
	  * @param IRTSAppEventListener $listener
	  */
	 public final function subscribeToAppEmitter(string $eventClass, IRTSAppEventListener $listener): void {
		$this->_emitter->subscribeToAppEmitter($eventClass,$listener);
	}
}