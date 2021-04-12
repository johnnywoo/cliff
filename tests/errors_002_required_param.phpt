--TEST--
Error test: required param not set
--ARGS--
a 2>&1
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->param('x')
    ->param('y')
);

?>
--EXPECT--
Need more arguments

Usage: errors_002_required_param.php [--help] <x> <y>

  --help  Show descriptions of options and params

PARAMETERS
  <x>
  <y>
