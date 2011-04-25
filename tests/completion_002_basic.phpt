--TEST--
Bash completion generator
--FILE--
<?php
include __DIR__ . '/_functions.inc';
use cliff\Cliff;
use cliff\Config;
use cliff\Completion;

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
draw_completon($config, 'x -');

echo "  one arg\n";
draw_completon($config, 'x --a');

echo "  empty\n";
draw_completon($config, 'x');

echo "  option value\n";
draw_completon($config, 'x --whatever=');

echo "  url value\n";
draw_completon($config, 'x --url=http://ex');

echo "  callback\n";
draw_completon($config, 'x --word=p');

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