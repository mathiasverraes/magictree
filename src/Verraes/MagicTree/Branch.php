<?php

namespace Verraes\MagicTree;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

final class Branch implements ArrayAccess, Iterator, JsonSerializable, Node, Countable
{
    protected $_children = [];

    /**
     * @param string $keys
     * @param string $key ...
     * @return bool
     */
    public function has()
    {
        $keys = func_get_args();
        $first = array_shift($keys);
        $hasFirstKey = array_key_exists($first, $this->_children);

        if(!$hasFirstKey) {
            return false;
        };
        if($hasFirstKey && 0==count($keys)) {
            return true;
        }
        return call_user_func_array([$this->_children[$first], 'has'], $keys);
    }

    public function remove($key)
    {
        unset($this->_children[$key]);
    }

    public function offsetGet($index)
    {
        if (!isset($this->_children[$index])) {
            $this->_children[$index] = new Branch();
        }
        return $this->_children[$index];
    }


    public function __get($name)
    {
        if (!isset($this->_children[$name])) {
            $this->_children[$name] = new Branch();
        }

        $child = $this->_children[$name];

        if ($child instanceof Leaf) {
            return $child->getValue();
        }

        return $child;
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function __set($name, $value)
    {
        if (is_scalar($value)) {
            $this->_children[$name] = new Leaf($value);

        } else {
            $this->_children[$name] = new Branch($value);
        }
    }

    public function __call($name, $arguments)
    {
        $this->$name = $arguments[0];
        return $this;
    }


    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \Exception("@todo  Implement offsetUnset() method.");
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->_children as $key => $child) {
            $result[$key] = $child instanceof Node ? $child->toArray() : $child;
        }
        return $result;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return false !== current($this->_children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->_children);
    }


    public function toAscii($indent = 0)
    {

        $output = '';

        foreach ($this->_children as $key => $child) {

            if($child instanceof Branch) {
                $value = "\n" . $child->toAscii($indent + 1);
            } elseif($child instanceof Leaf) {
                $value = $child->toAscii($indent + 1) . PHP_EOL;
            }

            $output .= str_repeat('  |', $indent) . '- ' . $key . $value;
        }

        return $output;
    }

    public function jsonSerialize()
    {
        return (object)array_combine(
            array_keys($this->_children),
            array_map(
                function ($child) {
                    return $child instanceof Node ? $child->jsonSerialize() : $child;
                },
                $this->_children
            )
        );
    }

    public function ksort(callable $comparator)
    {
        uksort($this->_children, $comparator);
    }

    public function sort(callable $comparator)
    {
        uasort($this->_children, $comparator);
    }

    public function filter(callable $decider)
    {
        foreach ($this->_children as $key => $child) {

            if ($decider($child)) {
                $this->remove($key);
            } else {
                if ($child instanceof Branch) {
                    $child->filter($decider);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_children);
    }

    public function where($keyToMatch, callable $callback)
    {
        foreach ($this->_children as $key => $child) {

            if ($key == $keyToMatch) {
                $callback($child);
            }

            if ($child instanceof Branch)  {
                $child->where($keyToMatch, $callback);
            }

        }

    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        if ($this->count() == 0) {
            return true;
        } else {
            foreach ($this->_children as $key => $child) {


                if (!$child->isEmpty()) {
                    return false;
                }
            }
        }

        return true;
    }
}
