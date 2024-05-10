<?php

class Strings
{
    public $value;
    public $start_position;
    public $end_position;
    public $context;

    public function __construct($value)
    {
        $this->value = $value;
        $this->set_position();
        $this->set_context();
    }

    public function set_position($start_position = null, $end_position = null)
    {
        $this->start_position = $start_position;
        $this->end_position = $end_position;

        return $this;
    }

    public function set_context($context = null)
    {
        $this->context = $context;

        return $this;
    }

    public function add($string)
    {
        if ($string instanceof Strings) {
            $result = new Strings($this->value.$string);

            return $result->set_context($this->context);
        }
    }

    public function is_true()
    {
        return strlen($this->value) > 0;
    }

    public function __toString()
    {
        return "{$this->value}";
    }
}
