--TEST--
Default values for options
--ARGS--
--val=123 --def --req-val=456 --req-def
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->option('--val', array('flagValue' => 'ZZZ'))
    ->option('--def', array('flagValue' => 'ZZZ'))
    ->option('--absent', array('flagValue' => 'NO WAI', 'default' => 'YA RLY'))
    ->option('--req-val', array('flagValue' => 'ZZZ', 'isRequired' => true))
    ->option('--req-def', array('flagValue' => 'ZZZ', 'isRequired' => true))
    // absence of a required option will cause an error
);

var_dump($_REQUEST);

?>
--EXPECT--
array(5) {
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
}
