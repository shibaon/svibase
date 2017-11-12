<?php

namespace Svi;

class ArrayAccess implements \ArrayAccess, \Iterator
{
    private $values = [];

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        $result = $this->values[$offset];

        if (is_callable($result)) {
            $this->values[$offset] = $result = $result();
        }

        return $result;
    }

    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function current()
    {
        return $this[$this->key()];
    }

    public function next()
    {
        next($this->values);
    }

    public function key()
    {
        return key($this->values);
    }

    public function valid()
    {
        return array_key_exists($this->key(), $this->values);
    }

    public function rewind()
    {
        reset($this->values);
    }

}