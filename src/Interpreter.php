<?php

class Interpreter
{
    public function visit($node, $context = null)
    {
        $class_name = (new \ReflectionClass($node))->getShortName();
        $method_name = 'visit_'.$class_name;

        if (method_exists($this, $method_name)) {
            return $this->$method_name($node, $context);
        } else {
            return $this->no_visit_method($node, $context);
        }
    }

    public function no_visit_method($node, $context)
    {
        $class_name = (new \ReflectionClass($node))->getShortName();
        throw new \Exception("No visit_{$class_name} method defined");
    }

    public function visit_FactorNode($node, $context)
    {
        $factor = new Numbers($node->token->value);
        $factor->set_context($context);
        $factor->set_position($node->start_position, $node->end_position);

        return $factor;
    }

    public function visit_BinaryOperationNode($node, $context)
    {
        $left_node = $this->visit($node->left_node, $context);
        $right_node = $this->visit($node->right_node, $context);
        $result = null;
        if ($node->operation_node->type == T_PLUS) {
            $result = $left_node->add($right_node);
        } elseif ($node->operation_node->type == T_MINUS) {
            $result = $left_node->subtract($right_node);
        } elseif ($node->operation_node->type == T_MUL) {
            $result = $left_node->multiply($right_node);
        } elseif ($node->operation_node->type == T_DIV) {
            $result = $left_node->divition($right_node);
        } elseif ($node->operation_node->type == T_POWER) {
            $result = $left_node->power($right_node);
        } elseif ($node->operation_node->type == T_EQUAL_EQUAL) {
            $result = $left_node->get_comparison_equal($right_node);
        } elseif ($node->operation_node->type == T_NOT_EQUAL) {
            $result = $left_node->get_comparison_not_equal($right_node);
        } elseif ($node->operation_node->type == T_GREATER_THAN) {
            $result = $left_node->get_comparison_greater_than($right_node);
        } elseif ($node->operation_node->type == T_GREATER_THAN_EQUAL) {
            $result = $left_node->get_comparison_greater_than_equal($right_node);
        } elseif ($node->operation_node->type == T_LESS_THAN) {
            $result = $left_node->get_comparison_less_than($right_node);
        } elseif ($node->operation_node->type == T_LESS_THAN_EQUAL) {
            $result = $left_node->get_comparison_less_than_equal($right_node);
        } elseif ($node->operation_node->matches(T_KEY_WORD, 'AND')) {
            $result = $left_node->and($right_node);
        } elseif ($node->operation_node->matches(T_KEY_WORD, 'OR')) {
            $result = $left_node->or($right_node);
        }
        if ($result instanceof CustomError) {
            return $result;
        }
        $result->set_position($node->start_position, $node->end_position);

        return $result;
    }

    public function visit_UnaryOperationNode($node, $context)
    {
        $factor = $this->visit($node->node, $context);
        if ($node->operation_node->type == T_MINUS) {
            $factor = $factor->multiply(new Numbers(-1));
        } elseif ($node->operation_node->matches(T_KEY_WORD, 'NOT')) {
            $factor = $factor->not();
        }
        $factor->set_position($node->start_position, $node->end_position);

        return $factor;
    }

    public function visit_VariableAssignNode($node, $context)
    {
        $variable_name = $node->variable_name->value;
        $value = $this->visit($node->value, $context);
        $context->symbol_table->set($variable_name, $value);

        return $value;
    }

    public function visit_VariableAccessNode($node, $context)
    {
        $variable_name = $node->variable_name->value;
        $value = $context->symbol_table->get($variable_name);
        if ($value == null) {
            return new RunTimeError($node->start_position, $node->end_position, "{$variable_name} is not defined");
        }

        return $value;
    }

    public function visit_IfNode($node, $context)
    {
        foreach ($node->cases as [$condition, $expression]) {
            $condition_value = $this->visit($condition, $context);
            if ($condition_value->is_true()) {
                return $this->visit($expression, $context);
            }
        }
        if ($node->else_case) {
            return $this->visit($node->else_case, $context);
        }

        return null;
    }

    public function visit_ForNode($node, $context)
    {
        $elements = [];
        $start_value = $this->visit($node->start_value_node, $context);
        $end_value = $this->visit($node->end_value_node, $context);
        if ($node->step_value_node) {
            $step_value = $this->visit($node->step_value_node, $context);
        } else {
            $step_value = new Numbers(1);
        }
        $i = $start_value->value;

        while (true) {
            if ($step_value->value >= 0) {
                $condition = function () use ($i, $end_value) { return $i < $end_value->value; };
            } else {
                $condition = function () use ($i, $end_value) { return $i > $end_value->value; };
            }
            if (!$condition()) {
                break;
            }
            $context->symbol_table->set($node->variable_name_token->value, new Numbers($i));
            $i += $step_value->value;
            array_push($elements, $this->visit($node->body_node, $context));
        }
        $result = new Lists($elements);

        return $result->set_context($context)->set_position($node->start_position, $node->end_position);
    }

    public function visit_WhileNode($node, $context)
    {
        $elements = [];
        while (true) {
            $condition = $this->visit($node->condition_node, $context);
            if (!$condition->is_true()) {
                break;
            }
            array_push($elements, $this->visit($node->body_node, $context));
        }
        $result = new Lists($elements);

        return $result->set_context($context)->set_position($node->start_position, $node->end_position);
    }

    public function visit_StringNode($node, $context)
    {
        $string = new Strings($node->token->value);
        $string->set_context($context)->set_position($node->start_position, $node->end_position);

        return $string;
    }

    public function visit_ListNode($node, $context)
    {
        $elements = [];
        foreach ($node->element_node as $element_node) {
            array_push($elements, $this->visit($element_node, $context));
        }
        $result = new Lists($elements);
        $result->set_context($context)->set_position($node->start_position, $node->end_position);

        return $result;
    }
}
