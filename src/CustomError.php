<?php

class CustomError
{
    public $start_position;
    public $end_position;
    public $error_type;
    public $details;

    public function __construct($start_position, $end_position, $error_type, $details)
    {
        $this->start_position = $start_position;
        $this->end_position = $end_position;
        $this->error_type = $error_type;
        $this->details = $details;
    }

    public function __toString()
    {
        $result = 'line '.$this->start_position->line + 1;
        $result .= ' column '.$this->start_position->index;
        $result .= " {$this->error_type}: {$this->details}\n";

        return $result;
    }
}

class IllegalCharError extends CustomError
{
    public function __construct($start_position, $end_position, $details)
    {
        parent::__construct($start_position, $end_position, 'Illegal Character', $details);
    }
}

class InvalidSyntaxError extends CustomError
{
    public function __construct($start_position, $end_position, $details)
    {
        parent::__construct($start_position, $end_position, 'Invalid Syntax', $details);
    }
}

class RunTimeError extends CustomError
{
    public function __construct($start_position, $end_position, $details)
    {
        parent::__construct($start_position, $end_position, 'Run Time Error', $details);
    }
}
