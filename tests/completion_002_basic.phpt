--TEST--
Bash completion generator
--FILE--
<?php

include __DIR__ . '/_functions.inc';
use Cliff\Cliff;
use Cliff\Config;

$config = Cliff::config()
    ->flag('--aaa')
    ->flag('--bbb')
    ->flag('--ccc')

    ->flag('--invisible', array(
        'visibility' => Config::V_ALL - Config::V_COMPLETION,
    ))

    ->option('--whatever', array(
        'completion' => array('one', 'two', 'three'),
    ))
    ->option('--url', array(
        'completion' => array('http://example.com/a', 'http://example.com/b', 'http://example.com/c'),
    ))
    ->option('--word', array(
        'completion' => function() {
            return array('el', 'pueblo', 'unido');
        },
    ));

echo "  args\n";
drawCompleton($config, 'x -');

echo "  one arg\n";
drawCompleton($config, 'x --a');

echo "  empty\n";
drawCompleton($config, 'x');

echo "  option value\n";
drawCompleton($config, 'x --whatever=');

echo "  url value\n";
drawCompleton($config, 'x --url=http://ex');

echo "  callback\n";
drawCompleton($config, 'x --word=p');

?>
--EXPECT--
  args
--aaa |
--bbb |
--ccc |
--whatever=|
--url=|
--word=|
---
  one arg
--aaa |
---
  empty
---
  option value
one |
two |
three |
---
  url value
//example.com/a |
//example.com/b |
//example.com/c |
---
  callback
pueblo |
---
