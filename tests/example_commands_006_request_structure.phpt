--TEST--
Branches example: request structure with subcommands
--ARGS--
subtract 10 1
--FILE--
<?php

include __DIR__ . '/../examples/commands.php';

var_dump($_REQUEST);

?>
--EXPECT--
9
array(4) {
  ["roman"]=>
  bool(false)
  ["precision"]=>
  string(1) "2"
  ["command"]=>
  string(8) "subtract"
  ["subtract"]=>
  array(2) {
    ["minuend"]=>
    string(2) "10"
    ["subtrahend"]=>
    string(1) "1"
  }
}
