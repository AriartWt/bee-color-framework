<?php
namespace wfw\engine\core\domain\events;

/**
 *  Liste d'événements
 */
final class EventList implements \Iterator,\Countable {
	/** @var IDomainEvent[] $_events */
	private $_events;
	/** @var int $_cursor */
	private $_cursor;

	/**
	 *  EventList constructor.
	 *
	 * @param array $events (optionnel) Tableau d'événements
	 */
	public function __construct(array $events=[]) {
		$this->_events = ((function(IDomainEvent ...$events){
			return $events;
		})(...$events));
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		return $this->_events[$this->_cursor];
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		$this->_cursor++;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return $this->_cursor;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		return $this->_cursor < count($this->_events);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		$this->_cursor = 0;
	}

	/**
	 * @return int
	 */
	public function getLength():int{
		return $this->count();
	}

	/**
	 * @return array
	 */
	public function toArray():array{
		return array_values($this->_events);
	}

	/**
	 * @param int $index
	 * @return IDomainEvent
	 */
	public function get(int $index):IDomainEvent{
		return $this->_events[$index];
	}

	/**
	 * @param IDomainEvent $domainEvent
	 */
	public function add(IDomainEvent $domainEvent){
		$this->_events[] = $domainEvent;
	}

	/**
	 * Count elements of an object
	 *
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count() {
		return count($this->_events);
	}
}