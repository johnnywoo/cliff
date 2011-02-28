--TEST--
Options example: help
--ARGS--
--help
--FILE--
<?php
include __DIR__ . '/../examples/options.php';
?>
--EXPECT--
Usage: example_options_002_help.php [-dtn] [options] <number>

Takes a number and multiplies it.

OPTIONS

  -d, --double
    Multiply the number by 2

  -t, --triple
    Multiply the number by 3

  -n, --negate
    Change sign of the number

  -m ..., --multiply=...
    Multiply by an arbitrary number

  --help
    Show descriptions of options and params

PARAMETERS

  number
    A number to operate on