--TEST--
Error test: too many params
--ARGS--
x y 2>&1
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->param('x')
);

?>
--EXPECT--
Too many arguments

Usage: errors_003_too_many_params.php [--help] <x>

  --help  Show descriptions of options and params

PARAMETERS
  <x>
