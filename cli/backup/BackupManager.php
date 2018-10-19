<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/04/18
 * Time: 01:52
 */

namespace wfw\cli\backup;

use wfw\cli\backup\errors\BackupNotFound;

/**
 * Gestionnaire de backups
 */
final class BackupManager implements IBackupManager
{
    /**
     * @var string[] $_orderedBackups
     */
    private $_orderedBackups;
    /**
     * @var IBackup[] $_backups
     */
    private $_backups;
    /**
     * @var int $_max
     */
    private $_max;

    /**
     * BackupManager constructor.
     *
     * @param int $maxBackup
     */
    public function __construct(int $maxBackup = 5)
    {
        $this->_backups = [];
        $this->_max = $maxBackup;
        $this->_orderedBackups = [];
    }

    /**
     * @param string $name Nom du backup à récupérer
     * @return IBackup
     */
    public function get(string $name): IBackup
    {
        if(!isset($this->_backups[$name]))
            throw new BackupNotFound("$name is not a valide backup name");
        return $this->_backups[$name];
    }

    /**
     * @param string  $name   Nom du backup à ajouter au manager
     * @param IBackup $backup Backup
     */
    public function add(string $name, IBackup $backup): void
    {
        if(isset($this->_backups[$name]))
            throw new \InvalidArgumentException("A backup with name $name already exists");
        $this->_backups[$name] = $backup;
        $this->_orderedBackups[] = $name;
        if($this->_max > 0 && count($this->_orderedBackups) > $this->_max)
            $this->remove($this->_orderedBackups[0]);
    }

    /**
     * @param string $name Nom du backup à supprimer
     */
    public function remove(string $name): void
    {
        if(!isset($this->_backups[$name]))
            throw new BackupNotFound("Backup $name not found");
        foreach($this->_orderedBackups as $k=>$v){
            if($v === $name){
                array_splice($this->_orderedBackups,$k,1);
                break;
            }
        }
        $this->_backups[$name]->remove();
        unset($this->_backups[$name]);
    }

    /**
     * @param int $max Nombre maximum de backups conservés.
     */
    public function changeMaxBackup(int $max): void{
        if($max > 0){
            $toDelete = count($this->_orderedBackups) - $max;
            while($toDelete > 0){
                $this->remove($this->_orderedBackups[0]);
                $toDelete--;
            }
        }
        $this->_max = $max;
    }

    /**
     * @param string $name Nom du backup à tester
     * @return bool True si le backup existe, false sinon
     */
    public function exists(string $name): bool { return isset($this->_backups[$name]); }

    /**
     * @var int $_cursor
     */
    private $_cursor;

    /**
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return IBackup Can return any type.
     * @since 5.0.0
     */
    public function current(){ return $this->_backups[$this->_orderedBackups[$this->_cursor]]; }

    /**
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() { $this->_cursor++; }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return string scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() { return $this->_orderedBackups[$this->_cursor]; }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() { return count($this->_orderedBackups) > $this->_cursor; }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(){ $this->_cursor = 0; }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     *                      The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset){
        if(is_integer($offset))
            return isset($this->_orderedBackups[$offset]);
        return isset($this->_backups[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return IBackup
     * @since 5.0.0
     */
    public function offsetGet($offset){
        if(is_integer($offset))
            return $this->get($this->_orderedBackups[$offset]);
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value){
        if(is_integer($offset))
            $this->add($this->_orderedBackups[$offset],$value);
        else
            $this->add($offset,$value);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        if(is_integer($offset))
            $this->remove($this->_orderedBackups[$offset]);
        else
            $this->remove($offset);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() { return count($this->_backups); }
}