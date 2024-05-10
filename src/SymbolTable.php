<?php

class SymbolTable
{
    public $symbols;
    public $parent;

    public function __construct($parent = null)
    {
        $this->symbols = [];
        $this->parent = $parent;
    }

    public function get($name)
    {
        if (array_key_exists($name, $this->symbols)) {
            return $this->symbols[$name];
        }

        if ($this->parent) {
            return $this->parent->get($name);
        }

        return null;
    }

    public function set($name, $value)
    {
        $this->symbols[$name] = $value;
    }

    public function remove($name)
    {
        if (array_key_exists($name, $this->symbols)) {
            unset($this->symbols[$name]);
        }
    }
}
