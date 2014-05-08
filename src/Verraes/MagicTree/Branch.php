<?php

namespace Verraes\MagicTree;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

final class Branch implements ArrayAccess, Iterator, JsonSerializable, Node, Countable
{
    /**
     * @var Node[]
     */
    protected $_children = [];

    /**
     * @param string $keyPart1
     * @param string $keyPart2
     * @param string $keyParts...
     * @return bool
     */
    public function has()
    {
        $keysParts = func_get_args();
        return $this->hasByKeyParts($keysParts);
    }

    public function remove($key)
    {
        unset($this->_children[$key]);
    }

    public function offsetGet($index)
    {
        return $this->getChild($index);
    }


    public function __get($name)
    {
        return $this->getChild($name);
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function __set($name, $value)
    {
        $this->addElement($name, $value);
    }

    private function addElement($name, $value)
    {
        if (is_scalar($value)) {
            $this->setNode($name, new Leaf($value));
        } else {
            $this->setNode($name, new Branch($value));
        }
    }

    public function __call($name, $arguments)
    {
        $this->addElement($name, $arguments[0]);
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
            } else {
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
        foreach ($this->_children as $node) {
            if (!$node->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $key
     * @return Node|mixed
     */
    private function getChild($key)
    {
        $node = $this->getNode($key);

        if ($node instanceof Leaf) {
            return $node->getValue();
        }

        return $node;
    }

    /**
     * @param $fromKeyParts
     * @param $toKeyParts
     */
    public function move($fromKeyParts, $toKeyParts)
    {
        if(!$this->hasByKeyParts($fromKeyParts)) {
            return;
        }

        $node = $this->getByKeyParts($fromKeyParts);

        $this->setByKeyParts($toKeyParts, $node);
        $this->removeByKeyParts($fromKeyParts);
    }

    /**
     * @param array $keyParts
     * @return Node
     */
    private function getByKeyParts(array $keyParts)
    {
        $first = array_shift($keyParts);
        $node = $this->getNode($first);

        if($node instanceof Branch && count($keyParts)) {
            return $node->getByKeyParts($keyParts);
        }

        return $node;
    }

    /**
     * @param array $keyParts
     * @param Node $newNode
     * @return void
     */
    private function setByKeyParts(array $keyParts, Node $newNode)
    {
        $first = array_shift($keyParts);
        $node = $this->getNode($first);

        if($node instanceof Branch && count($keyParts)) {
            $node->setByKeyParts($keyParts, $newNode);
            return;
        }

        $this->setNode($first, $newNode);
    }

    /**
     * @param array $keyParts
     * @return void
     */
    private function removeByKeyParts(array $keyParts)
    {
        $first = array_shift($keyParts);
        $node = $this->getNode($first);

        if($node instanceof Branch && count($keyParts)) {
            $node->removeByKeyParts($keyParts);
            return;
        }

        $this->remove($first);
    }

    private function hasByKeyParts(array $keyParts)
    {
        $first = array_shift($keyParts);

        if(!$this->hasNode($first)) {
            return false;
        }

        $node = $this->getNode($first);

        if(!count($keyParts)) {
            return true;
        }

        if($node instanceof Branch) {
            return $node->hasByKeyParts($keyParts);
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasNode($name)
    {
        return array_key_exists($name, $this->_children);
    }

    /**
     * @param $name
     * @return Node
     */
    private function getNode($name)
    {
        if (!$this->hasNode($name)) {
            $this->addElement($name, new Branch());
        }

        return $this->_children[$name];
    }

    /**
     * @param $name
     * @param Node $node
     */
    private function setNode($name, Node $node)
    {
        $this->_children[$name] = $node;
    }
}
