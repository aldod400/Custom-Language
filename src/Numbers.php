<?php

class Numbers
{
    public $value;
    public $context;
    public $start_position;
    public $end_position;

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
    }

    public function set_context($context = null)
    {
        $this->context = $context;

        return $this;
    }

    public function add($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value + $number->value);

            return $result->set_context($this->context);
        }
    }

    public function subtract($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value - $number->value);

            return $result->set_context($this->context);
        }
    }

    public function multiply($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value * $number->value);

            return $result->set_context($this->context);
        }
    }

    public function divition($number)
    {
        if (get_class($number) === 'Numbers') {
            if ($number->value == 0) {
                return new RunTimeError($number->start_position, $number->end_position, 'Division By Zero');
            }
            $result = new Numbers($this->value / $number->value);

            return $result->set_context($this->context);
        }
    }

    public function power($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value ** $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_equal($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value == $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_not_equal($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value != $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_greater_than($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value > $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_greater_than_equal($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value >= $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_less_than($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value < $number->value);

            return $result->set_context($this->context);
        }
    }

    public function get_comparison_less_than_equal($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value <= $number->value);

            return $result->set_context($this->context);
        }
    }

    public function and($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value && $number->value);

            return $result->set_context($this->context);
        }
    }

    public function or($number)
    {
        if (get_class($number) === 'Numbers') {
            $result = new Numbers($this->value || $number->value);

            return $result->set_context($this->context);
        }
    }

    public function not()
    {
        $result = new Numbers(($this->value == 0) ? 1 : 0);

        return $result->set_context($this->context);
    }

    public function is_true()
    {
        return $this->value != 0;
    }

    public function __toString()
    {
        return "{$this->value}";
    }
}
