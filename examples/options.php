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
	->desc('Takes a number and multiplies it.')
	->flag('--double -d', array(
		'Multiply the number by 2',
	))
	->flag('--triple -t', array(
		'Multiply the number by 3',
	))
	->flag('--negate -n', array(
		'Change sign of the number'
	))
	->option('--multiply -m', array(
		'Multiply by an arbitrary number',
		'validator' => 'is_numeric',
	))
	->param('number', array(
		'A number to operate on',
		'validator' => 'is_numeric',
	))
);

$number = $_REQUEST['number'];

if($_REQUEST['double'])
	$number *= 2;

if($_REQUEST['triple'])
	$number *= 3;

if($_REQUEST['negate'])
	$number *= -1;

if(!is_null($_REQUEST['multiply'])) // may be 0, so a strict check here (it will be NULL if the option was not given)
	$number *= $_REQUEST['multiply'];

echo "$number\n";

exit;


// if you're wondering, the usage text for this script will be:
?>
Usage: basic_options.php [-dtn] [options] <number>

Takes a number and multiplies it.

OPTIONS
  -d, --double  Multiply the number by 2
  -t, --triple  Multiply the number by 3
  -n, --negate  Change sign of the number
  -m ..., --multiply=...
                Multiply by an arbitrary number
  --help        Show descriptions of options and params

PARAMETERS
  number  A number to operate on
<?


// and --help will be a bit more sparse:
?>
Usage: basic_options.php [-dtn] [options] <number>

Takes a number and multiplies it.

OPTIONS

  -d, --double
	Multiply the number by 2

  -t, --triple
	Multiply the number by 3

  -n, --negate
	Change sign of the number

  -m ..., --multiply=...
	Multiply by an arbitrary number

  --help
	Show descriptions of options and params

PARAMETERS

  number
	A number to operate on
