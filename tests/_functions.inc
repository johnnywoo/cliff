<?php

// let's not affect autoload, we don't have too much files here
require_once __DIR__ . '/../lib/Cliff/Cliff.php';
require_once __DIR__ . '/../lib/Cliff/Exception.php';
require_once __DIR__ . '/../lib/Cliff/Exception/ParseError.php';
require_once __DIR__ . '/../lib/Cliff/Config.php';
require_once __DIR__ . '/../lib/Cliff/Parser.php';
require_once __DIR__ . '/../lib/Cliff/Tokenizer.php';
require_once __DIR__ . '/../lib/Cliff/Usage.php';
require_once __DIR__ . '/../lib/Cliff/Completion.php';

function drawParser($parser, $shortOptionsWithValues = '')
{
    if (!($parser instanceof Cliff\Parser)) {
        $args = $parser;
        if (is_string($args)) {
            $args = ($args == '') ? array() : explode(' ', $args);
        }

        $parser = new Cliff\Parser($args);
    }

    while ($row = $parser->read($shortOptionsWithValues)) {
        $s = array();
        foreach ($row as $k => $v) {
            $v = var_export($v, true);

            if ($k == 'type') {
                if ($v == Cliff\Parser::TYPE_PARAM) {
                    $v = 'param';
                }
                if ($v == Cliff\Parser::TYPE_OPTION) {
                    $v = 'option';
                }
            }
            $s[] = "$k: " . $v;
        }
        echo join(' ', $s) . "\n";
    }
    echo "---\n";
}

function drawTokenizer($string, $stopAtOffset = -1)
{
    // pipes are used so we don't have to add extra escaping madness,
    // but heading/trailing whitespace is still visible
    echo "> |$string|\n";
    $tk = new Cliff\Tokenizer($string);
    while ($arg = $tk->read($stopAtOffset)) {
        echo 'arg |' . $arg->arg . '|  word |' . $arg->word . "|\n";
    }
    echo "---\n";
}

function drawCompleton($config, $command, $pos = null)
{
    if ($pos === null) {
        $pos = strlen($command);
    }

    ob_start();
    Cliff\Completion::actionComplete($config, array($command, $pos, " \t\n\"'><=;|&(:"));
    $c = ob_get_clean();
    echo str_replace("\n", "|\n", $c);
    echo "---\n";
}
