<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/12/17
 * Time: 09:05
 */

namespace wfw\engine\lib\cli\argv;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Class ArgvParserResult
 *
 * @package wfw\engine\lib\cli\argv
 */
class ArgvParserResult implements \ArrayAccess,\Iterator
{
    private $_values;

    /**
     *  ArgvParserResult constructor.
     *
     * @param array $values Tableau clé/valeur optName->optValue
     */
    public function __construct(array $values)
    {
        $this->_values = $values;
    }

    /**
     *  Retourne la valeur d'une option
     *
     * @param string $optName Nom de l'option
     *
     * @return mixed
     */
    public function get(string $optName){
        if(isset($this->_values[$optName])){
            return $this->_values[$optName];
        }else{
            throw new \InvalidArgumentException("$optName is not a valide key !");
        }
    }

    /**
     *  Teste l'existence d'une option
     *
     * @param string $optName Nom de l'option dont on souhaite tester l'existence
     *
     * @return bool
     */
    public function exists(string $optName){
        return isset($this->_values[$optName]);
    }

    /**
     * @return int
     */
    public function getLength():int{
        return count($this->_values);
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->_values[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new IllegalInvocation("ArgvParserResult is immutable !");
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new IllegalInvocation("ArgvParserResult is immutable !");
    }

    private $_cursor;

    /**
     * Moves the cursor to the first (key,element) pair.
     * @return void
     */
    function rewind()
    {
        $this->_cursor = 0;
    }

    /**
     * Check if the cursor is on a valid element.
     * @return bool
     */
    function valid()
    {
        return $this->_cursor<count($this->_values);
    }

    /**
     * Returns the key of the current (key,element) pair under the cursor.
     * Prerequisite: the cursor must be over a valid element, or the behaviour
     * is unspecified; implementations may throw an unchecked exception to
     * help debugging programs.
     *
     * @return mixed
     */
    function key()
    {
        return array_keys($this->_values)[$this->_cursor];
    }

    /**
     * Returns the element of the current (key,element) pair under the cursor.
     * Prerequisite: the cursor must be over a valid element, or the behaviour
     * is unspecified; implementations may throw an unchecked exception to
     * help debugging programs.
     *
     * @return mixed
     */
    function current()
    {
        return array_values($this->_values)[$this->_cursor];
    }

    /**
     * Move the cursor to the next (key,element) pair, if any. If the cursor
     * is already beyond the last pair, does nothing.
		*
		* @return void
	*/
    function next() {
        $this->_cursor++;
    }
}