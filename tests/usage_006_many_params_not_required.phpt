--TEST--
No params should be allowed for non-required many-params
--ARGS--
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->manyParams('x', array(
        'isRequired' => false,
    ))
);

echo 'OK';

?>
--EXPECT--
OK
