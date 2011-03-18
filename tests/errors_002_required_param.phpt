--TEST--
Error test: required param not set
--ARGS--
a
--FILE--
<?php
include __DIR__ . '/_functions.inc';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->param('x')
	->param('y')
);

// unfortunately, STDERR is not validated against expected output,
// so we can only validate the FAIL is not there
echo 'FAIL';

?>
--EXPECT--
