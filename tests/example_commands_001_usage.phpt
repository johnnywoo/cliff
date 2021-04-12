--TEST--
Branches example: usage
--ARGS--
--FILE--
<?php

include __DIR__ . '/../examples/commands.php';

?>
--EXPECT--
Usage: example_commands_001_usage.php [-r] [--roman] [--precision=...] [--help] <command>

  -r, --roman      Output results in Roman numerals
  --precision=...  Output results with given precision
  --help           Show descriptions of options and params

COMMANDS
  add [--help] <summands>
    Add any numbers together
  subtract [--help] <minuend> <subtrahend>
    Subtract a number from another number
