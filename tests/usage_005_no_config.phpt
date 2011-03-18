--TEST--
No-config mode
--ARGS--
-ab --ccc --ddd=ZZZ e f
--FILE--
<?php
include __DIR__ . '/_functions.inc';

cliff\Cliff::run();

var_dump($_REQUEST);

?>
--EXPECT--
array(6) {
  ["args"]=>
  array(2) {
    [0]=>
    string(1) "e"
    [1]=>
    string(1) "f"
  }
  ["help"]=>
  bool(false)
  ["a"]=>
  bool(true)
  ["b"]=>
  bool(true)
  ["ccc"]=>
  bool(true)
  ["ddd"]=>
  string(3) "ZZZ"
}