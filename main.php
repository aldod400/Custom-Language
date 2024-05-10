<?php

require_once 'src/consts.php';
require_once 'src/Context.php';
require_once 'src/CustomError.php';
require_once 'src/Interpreter.php';
require_once 'src/Lexer.php';
require_once 'src/Lists.php';
require_once 'src/nodes.php';
require_once 'src/Numbers.php';
require_once 'src/Parser.php';
require_once 'src/Position.php';
require_once 'src/Strings.php';
require_once 'src/SymbolTable.php';
function clear_cmd()
{
    echo "\033[H\033[J";
}
error_reporting(E_ALL & ~E_WARNING);

$global_symbol_table = new SymbolTable();
$global_symbol_table->set('null', new Numbers(0));
$global_symbol_table->set('true', new Numbers(1));
$global_symbol_table->set('false', new Numbers(0));

function main()
{
    global $global_symbol_table;
    while (true) {
        echo 'Lexer> ';
        $text = readline();
        if (strtolower($text) == 'cls') {
            clear_cmd();
            continue;
        }
        if (strtolower($text) == 'exit') {
            break;
        }
        $lexer = new Lexer($text);
        [$tokens, $error] = $lexer->make_tokens();
        if ($error) {
            echo $error->__toString()."\n";
            continue;
        }

        $parser = new Parser($tokens);
        $ast = $parser->parse();
        if ($ast instanceof CustomError) {
            echo "\n".$ast->__toString();
            continue;
        }
        if ($ast->node instanceof CustomError) {
            echo "\n".$ast->node->__toString();
            continue;
        }
        $interpreter = new Interpreter();
        if ($interpreter instanceof CustomError) {
            echo $interpreter;
        }
        $context = new Context('<Programe>');
        $context->symbol_table = $global_symbol_table;
        $result = $interpreter->visit($ast, $context);
        if ($result) {
            echo $result;
        }
        echo "\n";
    }
}
main();
