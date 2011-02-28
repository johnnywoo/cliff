--TEST--
Default values for options
--ARGS--
--val=123 --def --req-val=456 --req-def
--FILE--
<?php
include __DIR__ . '/_functions.inc';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->option('--val', array('default' => 'ZZZ'))
	->option('--def', array('default' => 'ZZZ'))
	->option('--absent', array('default' => 'NO WAI', 'if_absent' => 'YA RLY'))
	->option('--req-val', array('default' => 'ZZZ', 'is_required' => true))
	->option('--req-def', array('default' => 'ZZZ', 'is_required' => true))
	// absence of a required option will cause an error
);

var_dump($_REQUEST);

?>
--EXPECT--
array(6) {
  ["val"]=>
  string(3) "123"
  ["def"]=>
  string(3) "ZZZ"
  ["absent"]=>
  string(6) "YA RLY"
  ["req-val"]=>
  string(3) "456"
  ["req-def"]=>
  string(3) "ZZZ"
  ["help"]=>
  bool(false)
}