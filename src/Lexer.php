<?php

class Token
{
    public $type;
    public $value;
    public $start_position;
    public $end_position;

    public function __construct($type, $value = null, $start_position = null, $end_position = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->start_position = $start_position;
        $this->end_position = $end_position;
    }

    public function matches($expected_type, $expected_value)
    {
        return $this->type == $expected_type && $this->value == $expected_value;
    }

    public function __toString()
    {
        if ($this->value === null) {
            return "{$this->type} ";
        } else {
            return "{$this->type} : {$this->value} ";
        }
    }
}

class Lexer
{
    public $text;
    public $position;
    public $current_char;

    public function __construct($text)
    {
        $this->text = $text;
        $this->position = new Position(0, 0, 0, $text);
        $this->next_char();
    }

    public function next_char()
    {
        $this->current_char = ($this->position->index < strlen($this->text)) ? $this->text[$this->position->index] : null;
        $this->position->next_position($this->current_char);
    }

    public function make_number()
    {
        $start_position = $this->position->current_position();
        $str = '';
        $numberOfDots = 0;
        while ($this->current_char !== null && (str_contains(DIGITS, $this->current_char) || $this->current_char === '.')) {
            if ($this->current_char == '.') {
                if ($numberOfDots == 1) {
                    break;
                }
                ++$numberOfDots;
                $str .= '.';
            } else {
                $str .= $this->current_char;
            }
            $this->next_char();
        }
        $end_position = $this->position->current_position();
        if ($numberOfDots === 0) {
            return new Token(T_INT, (int) $str, $start_position, $end_position);
        } else {
            return new Token(T_FLOAT, (float) $str, $start_position, $end_position);
        }
    }

    public function make_identifier()
    {
        $string = '';
        $start_position = $this->position->current_position();
        while ($this->current_char != null && (str_contains(LETTERS_DIGITS, $this->current_char) || $this->current_char == '_')) {
            $string .= $this->current_char;
            $this->next_char();
        }
        $token_type = (in_array($string, KEY_WORDS)) ? T_KEY_WORD : T_IDENTIFIER;

        return new Token($token_type, $string, $start_position, $this->position);
    }

    public function make_not_equal()
    {
        $start_position = $this->position->current_position();
        $this->next_char();
        if ($this->current_char == '=') {
            return new Token(T_NOT_EQUAL, '!=', $start_position, $this->position);
        }
        $this->next_char();

        return new InvalidSyntaxError($start_position, $this->position, 'Expexted = After !');
    }

    public function make_less_than()
    {
        $token_type = T_LESS_THAN;
        $token_value = '<';
        $start_position = $this->position->current_position();
        $this->next_char();
        if ($this->current_char == '=') {
            $this->next_char();
            $token_type = T_LESS_THAN_EQUAL;
            $token_value = '<=';
        }

        return new Token($token_type, $token_value, $start_position, $this->position);
    }

    public function make_greater_than()
    {
        $token_type = T_GREATER_THAN;
        $token_value = '>';
        $start_position = $this->position->current_position();
        $this->next_char();
        if ($this->current_char == '=') {
            $this->next_char();
            $token_type = T_GREATER_THAN_EQUAL;
            $token_value = '>=';
        }

        return new Token($token_type, $token_value, $start_position, $this->position);
    }

    public function make_string()
    {
        $string = '';
        $start_position = $this->position->current_position();
        $escape_char = false;
        $escape_chars = [
            'n' => "\n",
            't' => "\t",
        ];

        $this->next_char();
        while ($this->current_char && ($this->current_char != '"' || $escape_char)) {
            if ($escape_char) {
                $string .= $escape_chars[$this->current_char];
            } else {
                if ($this->current_char == '\\') {
                    $escape_char = true;
                } else {
                    $string .= $this->current_char;
                }
            }
            $this->next_char();
            $escape_char = false;
        }
        $this->next_char();

        return new Token(TT_STRING, $string, $start_position, $this->position);
    }

    public function make_tokens()
    {
        $tokens = [];

        while ($this->current_char !== null) {
            if ($this->current_char === ' ' || $this->current_char === "\t") {
                $this->next_char();
                continue;
            } elseif (str_contains(DIGITS, $this->current_char)) {
                array_push($tokens, $this->make_number());
                continue;
            } elseif (str_contains(LETTERS, $this->current_char)) {
                array_push($tokens, $this->make_identifier());
                continue;
            } elseif ($this->current_char == '"') {
                array_push($tokens, $this->make_string());
                continue;
            } elseif ($this->current_char == '$') {
                array_push($tokens, new Token(T_KEY_WORD, '$'));
            } elseif ($this->current_char == '+') {
                array_push($tokens, new Token(T_PLUS));
            } elseif ($this->current_char == '-') {
                array_push($tokens, new Token(T_MINUS));
            } elseif ($this->current_char == '*') {
                array_push($tokens, new Token(T_MUL));
            } elseif ($this->current_char == '/') {
                array_push($tokens, new Token(T_DIV));
            } elseif ($this->current_char == '^') {
                array_push($tokens, new Token(T_POWER));
            } elseif ($this->current_char == '=') {
                array_push($tokens, new Token(T_EQUAL, '='));
                $this->next_char();
                if ($this->current_char == '=') {
                    array_pop($tokens);
                    array_push($tokens, new Token(T_EQUAL_EQUAL, '=='));
                    $this->next_char();
                }
                continue;
            } elseif ($this->current_char == '!') {
                $not_equal = $this->make_not_equal();
                if ($not_equal instanceof CustomError) {
                    return [[], $not_equal];
                }
                array_push($tokens, $not_equal);
            } elseif ($this->current_char == '>') {
                array_push($tokens, $this->make_greater_than());
                continue;
            } elseif ($this->current_char == '<') {
                array_push($tokens, $this->make_less_than());
                continue;
            } elseif ($this->current_char == ',') {
                array_push($tokens, new Token(T_COMMA, ',', $this->position));
            } elseif ($this->current_char == '(') {
                array_push($tokens, new Token(T_LPAREN));
            } elseif ($this->current_char == ')') {
                array_push($tokens, new Token(T_RPAREN));
            } elseif ($this->current_char == '[') {
                array_push($tokens, new Token(T_LSQUARE, '['));
            } elseif ($this->current_char == ']') {
                array_push($tokens, new Token(T_RSQUARE, ']'));
            } else {
                $start_position = $this->position->current_position();
                $spaces = [' ', "\t", "\n", "\0"];
                $error = '';
                while ($this->current_char && !in_array($this->current_char, $spaces)) {
                    $error .= $this->current_char;
                    $this->next_char();
                }

                return [[], new IllegalCharError($start_position, $this->position, "'".$error."'")];
            }
            $this->next_char();
        }

        return [$tokens, null];
    }
}
