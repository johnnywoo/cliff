--TEST--
Required option set correctly
--ARGS--
-x KEKEKE
--FILE--
<?php
include __DIR__ . '/_functions.inc';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->option('-x', array(
		'Whatever',
		'is_required' => true,
	))
);

var_dump($_REQUEST);

?>
--EXPECT--
array(2) {
  ["x"]=>
  string(6) "KEKEKE"
  ["help"]=>
  bool(false)
}