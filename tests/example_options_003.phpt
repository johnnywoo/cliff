--TEST--
Options example: long options
--ARGS--
--negate --multiply=7 --triple -- -2
--FILE--
<?php

include __DIR__ . '/../examples/options.php';

?>
--EXPECT--
42
