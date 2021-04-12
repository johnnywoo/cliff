--TEST--
No-config mode
--ARGS--
-ab --ccc --ddd=ZZZ e f
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';

Cliff\Cliff::run();

var_dump($_REQUEST);

?>
--EXPECT--
array(5) {
  ["args"]=>
  array(2) {
    [0]=>
    string(1) "e"
    [1]=>
    string(1) "f"
  }
  ["a"]=>
  bool(true)
  ["b"]=>
  bool(true)
  ["ccc"]=>
  bool(true)
  ["ddd"]=>
  string(3) "ZZZ"
}
