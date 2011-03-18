--TEST--
No params should be allowed for non-required many-params
--ARGS--
--FILE--
<?php
include __DIR__ . '/_functions.inc';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->many_params('x', array(
		'is_required' => false,
	))
);

echo 'OK';

?>
--EXPECT--
OK