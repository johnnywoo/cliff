--TEST--
Options example: usage
--ARGS--
--FILE--
<?php

include __DIR__ . '/../examples/options.php';

?>
--EXPECT--
Usage: example_options_001_usage.php [-dtn] [options] <number>

  -d, --double  Multiply the number by 2
  -t, --triple  Multiply the number by 3
  -n, --negate  Change sign of the number
  -m ..., --multiply=...
                Multiply by an arbitrary number
  --help        Show descriptions of options and params

PARAMETERS
  <number>  A number to operate on
