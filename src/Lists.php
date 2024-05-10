<?php

class Lists
{
    public $elements;
    public $start_position;
    public $end_position;
    public $context;

    public function __construct($elements)
    {
        $this->elements = $elements;
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

    public function add($element)
    {
        $new_list = $this->copy();
        array_push($new_list->elements, $element);

        return $new_list;
    }

    public function subtract($element)
    {
        if ($element instanceof Numbers) {
            $new_list = $this->copy();
            try {
                array_splice($new_list->elements, $element->value, 1);

                return $new_list;
            } catch (Exception $exception) {
                echo $exception;
            }
        }
    }

    public function multiply($element)
    {
        if ($element instanceof Lists) {
            $new_list = $this->copy();

            $new_list->elements = array_merge($new_list->elements, $element->elements);

            return $new_list;
        }
    }

    public function divition($element)
    {
        if ($element instanceof Numbers) {
            try {
                return $this->elements[$element->value];
            } catch (Exception $exception) {
                echo $exception;
            }
        }
    }

    public function copy()
    {
        $copy = new Lists($this->elements);
        $copy->set_position($this->start_position, $this->end_position);
        $copy->set_context($this->context);

        return $copy;
    }

    public function __toString()
    {
        $elementStrings = array_map(function ($x) {
            return (string) $x;
        }, $this->elements);

        return '['.implode(', ', $elementStrings).']';
    }
}
