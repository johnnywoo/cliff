--TEST--
Branches example: usage of a subcommand
--ARGS--
add 2>&1
--FILE--
<?php

include __DIR__ . '/../examples/commands.php';

?>
--EXPECT--
Need more arguments

Usage: add [--help] <summands>

  --help  Show descriptions of options and params

PARAMETERS
  <summands>  Numbers that will be added together
