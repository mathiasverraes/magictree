<?php

namespace Verraes\MagicTree;

final class Leaf implements Node
{
    private $_value = '';

    public function __construct($_value = '')
    {
        $this->_value = $_value;
    }

    public function __toString()
    {
        return (string)$this->_value;
    }

    public function toArray()
    {
        return (string)$this;
    }

    public function toAscii($indent = 0)
    {
        return ': "' . $this->_value . '"';
    }

    public function jsonSerialize()
    {
        return $this->_value;
    }
}
