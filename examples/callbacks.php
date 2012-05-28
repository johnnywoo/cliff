<?php

/**
 * Example config: a basic issue tracker fetcher interface
 */

require_once __DIR__.'/../lib/Cliff.php';
use \cliff\Cliff;

Cliff::run(
	Cliff::config()
	->desc('Describes issues from an issue tracker')
	->flag('--version -v', array(
		'Output version info',
		'callback' => function() {
			// The callback is called immediately when --list (or -l) is read
			// from script arguments, before parsing is done completely.
			// So we can ignore the rest of arguments by exiting the script here.
			echo "Powerful Issue Describer version 1.0.0\n";
			exit;
		},
	))
	->option('--issue -i', array(
		'Narrow the list by ID or a branch name
		Let\'s say we have the following git branch naming convention:
		author_1234_description
		where 1234 is our issue ID. We can support this format transparently
		by modifying the parameter from the validation callback.',
		'validator' => function(&$value) {
			// we have $value by reference here, and therefore can modify it
			if(preg_match('/^[^_]+_(\d+)/', $value, $m))
				$value = $m[1];
			return is_numeric($value);
		},
	))
	->option('--tracker', array(
		'Tracker URL',
		'completion' => array(
			'http://example.com/default-tracker',
			'http://example.com/special-tracker',
			'http://example.com/special-tracker2',
		),
	))
	->option('--type', array(
		'Issue type',
		'completion' => function($entered_value) {
			// you don't have to sort or even match the correct variants,
			// that is all done automatically
			return array('bug', 'feature', 'task', 'meta', 'project');
		},
	))
);

/**
 * We have modified this value in the validation callback, so even if
 * the script is called with a branch name instead of issue ID, the
 * issue variable here will be numeric ID.
 */
$issue = $_REQUEST['issue'];
if($issue)
	echo "One issue info requested: issue ID $issue\n";
else
	echo "All issues info requested\n";

// Note that we didn't add params in our config, so the script
// will not display usage if called with no arguments.
