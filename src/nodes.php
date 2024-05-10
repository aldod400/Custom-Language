<?php

class FactorNode
{
    public $token;
    public $start_position;
    public $end_position;

    public function __construct($token)
    {
        $this->token = $token;
        $this->start_position = $this->token->start_position;
        $this->end_position = $this->token->end_position;
    }

    public function __toString()
    {
        return (string) $this->token;
    }
}

class BinaryOperationNode
{
    public $left_node;
    public $operation_node;
    public $right_node;
    public $start_position;
    public $end_position;

    public function __construct($left_node, $operation_node, $right_node)
    {
        $this->left_node = $left_node;
        $this->operation_node = $operation_node;
        $this->right_node = $right_node;
        $this->start_position = $this->left_node->start_position;
        $this->end_position = $this->right_node->end_position;
    }

    public function __toString()
    {
        return "\n{{$this->left_node} , {$this->operation_node} , {$this->right_node}}\n";
    }
}

class UnaryOperationNode
{
    public $operation_node;
    public $node;
    public $start_position;
    public $end_position;

    public function __construct($operation_node, $node)
    {
        $this->operation_node = $operation_node;
        $this->node = $node;
        $this->start_position = $this->operation_node->start_position;
        $this->end_position = $this->node->end_position;
    }

    public function __toString()
    {
        return "\n{{$this->operation_node}, {$this->node}}\n";
    }
}

class VariableAssignNode
{
    public $variable_name;
    public $value;
    public $start_position;
    public $end_position;

    public function __construct($variable_name, $value)
    {
        $this->variable_name = $variable_name;
        $this->value = $value;
        $this->start_position = $this->variable_name->start_position;
        $this->end_position = $this->variable_name->end_position;
    }

    public function __toString()
    {
        return "{$this->variable_name} = {$this->value}";
    }
}

class VariableAccessNode
{
    public $variable_name;
    public $start_position;
    public $end_position;

    public function __construct($variable_name)
    {
        $this->variable_name = $variable_name;
        $this->start_position = $variable_name->start_position;
        $this->end_position = $variable_name->end_position;
    }

    public function __toString()
    {
        return "{$this->variable_name}";
    }
}

class IfNode
{
    public $cases;
    public $else_case;
    public $start_position;
    public $end_position;

    public function __construct($cases, $else_case = null)
    {
        $this->cases = $cases;
        $this->else_case = $else_case;

        $this->start_position = $this->cases[0][0]->start_position;

        if ($this->else_case) {
            $this->end_position = $this->else_case->end_position;
        } else {
            $this->end_position = $this->cases[count($this->cases) - 1][0]->end_position;
        }
    }

    public function __toString()
    {
        return "{$this->cases[0][0]}";
    }
}

class ForNode
{
    public $variable_name_token;
    public $start_value_node;
    public $end_value_node;
    public $step_value_node;
    public $body_node;
    public $start_position;
    public $end_position;

    public function __construct($variable_name_token, $start_value_node, $end_value_node, $step_value_node, $body_node)
    {
        $this->variable_name_token = $variable_name_token;
        $this->start_value_node = $start_value_node;
        $this->end_value_node = $end_value_node;
        $this->step_value_node = $step_value_node;
        $this->body_node = $body_node;
        $this->start_position = $this->variable_name_token->start_position;
        $this->end_position = $this->body_node->end_position;
    }

    public function __toString()
    {
        return "{$this->end_value_node}";
    }
}

class WhileNode
{
    public $condition_node;
    public $body_node;
    public $start_position;
    public $end_position;

    public function __construct($condition_node, $body_node)
    {
        $this->condition_node = $condition_node;
        $this->body_node = $body_node;
        $this->start_position = $this->condition_node->start_position;
        $this->end_position = $this->body_node->end_position;
    }

    public function __toString()
    {
        return "{$this->body_node}";
    }
}

class StringNode
{
    public $token;
    public $start_position;
    public $end_position;

    public function __construct($token)
    {
        $this->token = $token;
        $this->start_position = $this->token->start_position;
        $this->end_position = $this->token->end_position;
    }

    public function __toString()
    {
        return (string) $this->token;
    }
}

class ListNode
{
    public $element_node;
    public $start_position;
    public $end_position;

    public function __construct($element_node, $start_position, $end_position)
    {
        $this->element_node = $element_node;
        $this->start_position = $start_position;
        $this->end_position = $end_position;
    }

    public function __toString()
    {
        foreach ($this->element_node as $value) {
            return (string) $value;
        }
    }
}
