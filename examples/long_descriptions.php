<?php

/**
 * Example config: a script that outputs a modified number
 *
 * Usage: script [-dtn] [-m N] [--double] [--triple] [--negate] [--multiply=N] number
 */

require_once __DIR__.'/../lib/Cliff.php';
use \cliff\Cliff;

Cliff::run(
	Cliff::config()
	->desc('Takes a number and multiplies it.
		Yeah, so like multiplication is a complex operation on numbers,
		making them usually very huge, which is cool because huge things
		are totally cool in every possible way.'
	)
	->option('--double -d', true, array( // note that you need a non-null default here
		'Multiply the number by 2',
	))
	->option('--triple -t', true, array(
		'Multiply the number by 3',
	))
	->option('--negate -n', true, array(
		'Change sign of the number'
	))
	->option('--multiply -m', null, array( // null default means this option requires a value
		'Multiply by an arbitrary number',
		'validator' => 'is_numeric',
	))
	->param('number', array(
		'A number to operate on',
		'validator' => 'is_numeric',
	))
);
