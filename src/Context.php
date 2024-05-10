<?php

class Context
{
    public $display_name;
    public $parent;
    public $parent_entry_position;
    public $symbol_table;

    public function __construct($display_name, $parent = null, $parent_entry_position = null)
    {
        $this->display_name = $display_name;
        $this->parent = $parent;
        $this->parent_entry_position = $parent_entry_position;
        $this->symbol_table = null;
    }
}
