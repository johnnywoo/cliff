--TEST--
Error test: required option not set
--ARGS--
--FILE--
<?php
include __DIR__ . '/../lib/Cliff.php';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->option('-x', array(
		'Whatever',
		'is_required' => true,
	))
);

?>
--EXPECT--
Need value for -x

Usage: errors_001_required_option.php [-x ...] [--help]

OPTIONS
  -x ...  Whatever
  --help  Show descriptions of options and params