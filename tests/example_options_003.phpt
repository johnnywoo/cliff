--TEST--
Options example: long options
--ARGS--
--negate --multiply=7 --double --triple -- -1
--FILE--
<?php
include __DIR__ . '/../examples/options.php';
?>
--EXPECT--
42