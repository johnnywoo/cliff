--TEST--
Default values for options
--ARGS--
-abc --ddd --eee=ZZZ
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->allowUnknownOptions()
);

var_dump($_REQUEST);

?>
--EXPECT--
array(5) {
  ["a"]=>
  bool(true)
  ["b"]=>
  bool(true)
  ["c"]=>
  bool(true)
  ["ddd"]=>
  bool(true)
  ["eee"]=>
  string(3) "ZZZ"
}
