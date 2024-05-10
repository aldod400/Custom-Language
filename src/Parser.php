<?php

class Parser
{
    public $tokens;
    public $token_index;
    public $current_token;

    public function __construct($tokens)
    {
        $this->tokens = $tokens;
        $this->token_index = 0;
        $this->next_token();
    }

    public function next_token()
    {
        $this->current_token = ($this->token_index < count($this->tokens)) ? $this->tokens[$this->token_index] : null;
        ++$this->token_index;

        return $this->current_token;
    }

    public function if_expression()
    {
        $cases = [];
        $else_case = null;
        if (!$this->current_token->matches(T_KEY_WORD, 'IF')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected IF'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected epression'
            );
        }
        $condition = $this->solve_expression();
        if ($this->current_token == null || !$this->current_token->matches(T_KEY_WORD, 'THEN')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected THEN'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected epression'
            );
        }
        $expression = $this->solve_expression();
        array_push($cases, [$condition, $expression]);

        while ($this->current_token && $this->current_token->matches(T_KEY_WORD, 'ELSEIF')) {
            $this->next_token();
            $condition = $this->solve_expression();
            if ($this->current_token == null || !$this->current_token->matches(T_KEY_WORD, 'THEN')) {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expected THEN'
                );
            }
            $this->next_token();
            if ($this->current_token == null) {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expected epression'
                );
            }
            $expression = $this->solve_expression();
            array_push($cases, [$condition, $expression]);
        }
        if ($this->current_token && $this->current_token->matches(T_KEY_WORD, 'ELSE')) {
            $this->next_token();
            global $else_case;
            $else_case = $this->solve_expression();
        }

        return new IfNode($cases, $else_case);
    }

    public function for_expression()
    {
        if (!$this->current_token->matches(T_KEY_WORD, 'FOR')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected FOR'
            );
        }
        $this->next_token();
        if ($this->current_token == null || !$this->current_token->type == T_IDENTIFIER) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected identifier'
            );
        }
        $variable_name = $this->current_token;
        $this->next_token();
        if ($this->current_token == null || $this->current_token->type != T_EQUAL) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected Equal(=)'
            );
        }

        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected expression'
            );
        }
        $start_value = $this->solve_expression();
        if ($this->current_token == null || !$this->current_token->matches(T_KEY_WORD, 'TO')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected TO'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected expression'
            );
        }
        $end_value = $this->solve_expression();
        if ($this->current_token->matches(T_KEY_WORD, 'STEP')) {
            $this->next_token();
            $step_value = $this->solve_expression();
        } else {
            $step_value = null;
        }
        if ($this->current_token == null || !$this->current_token->matches(T_KEY_WORD, 'THEN')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected THEN'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected expression'
            );
        }
        $body = $this->solve_expression();

        return new ForNode($variable_name, $start_value, $end_value, $step_value, $body);
    }

    public function while_expression()
    {
        if (!$this->current_token->matches(T_KEY_WORD, 'WHILE')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected WHILE'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected expression'
            );
        }
        $condition = $this->solve_expression();
        if ($this->current_token == null || !$this->current_token->matches(T_KEY_WORD, 'THEN')) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected THEN'
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected expression'
            );
        }
        $body = $this->solve_expression();

        return new WhileNode($condition, $body);
    }

    public function list_expression()
    {
        $element_node = [];
        $start_position = 0;
        if ($this->current_token == null || $this->current_token->type != T_LSQUARE) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected ['
            );
        }
        $this->next_token();
        if ($this->current_token == null) {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expected Comma(,) or ]'
            );
        }
        if ($this->current_token->type == T_RSQUARE) {
            $this->next_token();
        } else {
            array_push($element_node, $this->solve_expression());
            while ($this->current_token->type == T_COMMA) {
                $this->next_token();
                array_push($element_node, $this->solve_expression());
            }
            if ($this->current_token == null || $this->current_token->type != T_RSQUARE) {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expected Comma(,) or ]'
                );
            }
            $this->next_token();
        }
        $end_position = 0;

        return new ListNode($element_node, $start_position, $end_position);
    }

    public function atom()
    {
        if ($this->current_token != null) {
            $token = $this->current_token;
            if ($this->current_token && ($this->current_token->type == T_INT || $this->current_token->type == T_FLOAT)) {
                $this->next_token();

                return new FactorNode($token);
            }if ($this->current_token && $this->current_token->type == TT_STRING) {
                $this->next_token();

                return new StringNode($token);
            } elseif ($this->current_token->type == T_IDENTIFIER) {
                $this->next_token();

                return new VariableAccessNode($token);
            } elseif ($this->current_token && ($this->current_token->type == T_LPAREN)) {
                $this->next_token();
                $solve_expression = $this->solve_expression();
                if ($this->current_token->type == T_RPAREN) {
                    $this->next_token();

                    return $solve_expression;
                } else {
                    return new InvalidSyntaxError(
                        $this->current_token->start_position,
                        $this->current_token->end_position,
                        'Expect )'
                    );
                }
            } elseif ($this->current_token->type == T_LSQUARE) {
                return $this->list_expression();
            } elseif ($this->current_token->matches(T_KEY_WORD, 'IF')) {
                return $this->if_expression();
            } elseif ($this->current_token->matches(T_KEY_WORD, 'FOR')) {
                return $this->for_expression();
            } elseif ($this->current_token->matches(T_KEY_WORD, 'WHILE')) {
                return $this->while_expression();
            } else {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expect INT, Float, While, For, If, ['
                );
            }
        } else {
            return new InvalidSyntaxError(
                $this->current_token->start_position,
                $this->current_token->end_position,
                'Expect INT, Float, While, For, If, ['
            );
        }
    }

    public function power()
    {
        $left_node = $this->atom();
        while ($this->current_token && ($this->current_token->type == T_POWER)) {
            $operation_node = $this->current_token;
            $this->next_token();
            $right_node = $this->factor();

            $left_node = new BinaryOperationNode($left_node, $operation_node, $right_node);
        }

        return $left_node;
    }

    public function factor()
    {
        $token = $this->current_token;

        if ($this->current_token && ($this->current_token->type == T_PLUS || $this->current_token->type == T_MINUS)) {
            $this->next_token();
            $factor = $this->factor();

            return new UnaryOperationNode($token, $factor);
        }

        return $this->power();
    }

    public function solve_term()
    {
        $left_node = $this->factor();

        while ($this->current_token && ($this->current_token->type == T_MUL || $this->current_token->type == T_DIV)) {
            $operation_node = $this->current_token;
            $this->next_token();
            $right_node = $this->factor();

            $left_node = new BinaryOperationNode($left_node, $operation_node, $right_node);
        }

        return $left_node;
    }

    public function solve_arithmatic_expression()
    {
        $left_node = $this->solve_term();

        while ($this->current_token && ($this->current_token->type == T_PLUS || $this->current_token->type == T_MINUS)) {
            $operation_node = $this->current_token;
            $this->next_token();
            $right_node = $this->solve_term();

            $left_node = new BinaryOperationNode($left_node, $operation_node, $right_node);
        }

        return $left_node;
    }

    public function solve_comparison_expression()
    {
        if ($this->current_token->matches(T_KEY_WORD, 'NOT')) {
            $operation_token = $this->current_token;
            $this->next_token();
            $node = $this->solve_comparison_expression();

            return new UnaryOperationNode($operation_token, $node);
        }
        $left_node = $this->solve_arithmatic_expression();

        while ($this->current_token && ($this->current_token->type == T_EQUAL_EQUAL || $this->current_token->type == T_NOT_EQUAL
                || $this->current_token->type == T_GREATER_THAN || $this->current_token->type == T_GREATER_THAN_EQUAL
                || $this->current_token->type == T_LESS_THAN || $this->current_token->type == T_LESS_THAN_EQUAL)) {
            $operation_node = $this->current_token;
            $this->next_token();
            $right_node = $this->solve_arithmatic_expression();

            $left_node = new BinaryOperationNode($left_node, $operation_node, $right_node);
        }

        return $left_node;
    }

    public function solve_expression()
    {
        if ($this->current_token->matches(T_KEY_WORD, '$')) {
            $this->next_token();
            if ($this->current_token == null || $this->current_token->type != T_IDENTIFIER) {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expected Identifier'
                );
            }
            $variable_name = $this->current_token;
            $this->next_token();
            if ($this->current_token == null || $this->current_token->type != T_EQUAL) {
                return new InvalidSyntaxError(
                    $variable_name->end_position,
                    $variable_name->end_position,
                    'Expected Equal(=)'
                );
            }
            $this->next_token();
            if ($this->current_token == null) {
                return new InvalidSyntaxError(
                    $this->current_token->start_position,
                    $this->current_token->end_position,
                    'Expected expression'
                );
            }
            $expression = $this->solve_expression();

            return new VariableAssignNode($variable_name, $expression);
        }
        $left_node = $this->solve_comparison_expression();

        while ($this->current_token && (($this->current_token->type == T_KEY_WORD && $this->current_token->value == 'AND')
                || ($this->current_token->type == T_KEY_WORD && $this->current_token->value == 'OR'))) {
            $operation_node = $this->current_token;
            $this->next_token();
            $right_node = $this->solve_comparison_expression();

            $left_node = new BinaryOperationNode($left_node, $operation_node, $right_node);
        }

        return $left_node;
    }

    public function parse()
    {
        return $this->solve_expression();
    }
}
