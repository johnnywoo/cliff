--TEST--
Error test: required option not set
--ARGS--
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->option('-x', array(
        'Whatever',
        'isRequired' => true,
    ))
);

?>
--EXPECT--
Need value for -x

Usage: errors_001_required_option.php [-x ...] [--help]

  -x ...  Whatever
  --help  Show descriptions of options and params
