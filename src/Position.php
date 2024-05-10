<?php

class Position
{
    public $index;
    public $line;
    public $col;
    public $text;

    public function __construct($index, $line, $col, $text)
    {
        $this->index = $index;
        $this->line = $line;
        $this->col = $col;
        $this->text = $text;
    }

    public function next_position($current_char = null)
    {
        ++$this->index;
        ++$this->col;
        if ($current_char == "\n") {
            $this->index = 0;
            ++$this->col;
        }

        return $this;
    }

    public function current_position()
    {
        return new Position($this->index, $this->line, $this->col, $this->text);
    }
}
