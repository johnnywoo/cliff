<?php

/*

DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

Cliff: a CLI framework for PHP.
Copyright 2011 Aleksandr Galkin.

This file is part of Cliff.

Cliff is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

Cliff is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Cliff. If not, see <http://www.gnu.org/licenses/>.

*/

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
