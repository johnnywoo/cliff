<?php

/**
 * Example config: branches in usage structure
 */

require_once __DIR__.'/../lib/Cliff/Cliff.php';
use \Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->desc('A simple calculator with subcommands')

    // COMMON OPTIONS

    ->flag('--roman -r', 'Output results in Roman numerals')
    ->option('--precision', array(
        'Output results with given precision',
        'default' => '2',
        'validator' => function($value) {
            return is_numeric($value) && (intval($value) == $value);
        },
    ))

    // SUBCOMMANDS

    // There's an implicit call here:
    // ->param('command', array(
    //     'use_for_commands' => true,
    // ))
    // You can include 'use_for_commands' in a param of your own
    // to change the name, make it non-required or anything else.

    ->command('add', Cliff::config()
        ->desc('
            Add any numbers together

            We just take any numbers and add them together.
        ')
        ->manyParams('summands', 'Numbers that will be added together')
    )

    // 'subtract' is the main command name, 'minus' is an alias (useful for short names like `git st`).
    // $_REQUEST and command param will always use the main (first) name.
    ->command('subtract minus', Cliff::config()
        ->desc('
            Subtract a number from another number

            Unlike addition, we can only accept two arguments, because subtraction is not
            commutative (if you swapped the numbers, the result would be different).
        ')
        ->param('minuend', array(
            'A number we start with',
            'validator' => 'is_numeric',
        ))
        ->param('subtrahend', array(
            'A number we subtract from the other one',
            'validator' => 'is_numeric',
        ))
    )
);

// Now, let's see what will happen with such a config.
// Global options are, as always, accumulated in $_REQUEST:
$precision = $_REQUEST['precision'];
$isRoman   = $_REQUEST['roman'];

$result = null;

// Command options and params are grouped by command name.
// This way you can always tell a global option from a local one;
// it also allows for nested subcommands.

// To tell which command was requested, use the command param $_REQUEST['command'].
// You can change the name by providing a param with 'use_for_commands' = true.

$cmdRequest = $_REQUEST['command'] ? $_REQUEST[$_REQUEST['command']] : null;
switch ($_REQUEST['command']) {
    case 'add':
        $result = array_sum($cmdRequest['summands']);
        break;

    case 'subtract':
        $result = $cmdRequest['minuend'] - $cmdRequest['subtrahend'];
        break;
}

// By default, the command param is required, so we can safely assume
// there's a result we need to output.

if ($isRoman) {
    require_once 'Numbers/Roman.php';
    if (!class_exists('\Numbers_Roman')) {
        throw new Exception('You need Numbers_Roman from PEAR to output results as Roman numerals');
    }

    $nr = new \Numbers_Roman();
    echo $nr->toNumeral(intval($result)) . "\n";
} else {
    echo round($result, $precision) . "\n";
}
