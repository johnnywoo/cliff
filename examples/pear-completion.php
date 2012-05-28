<?php

/**
 * This is a completion script for PEAR
 *
 * You can use this to enable basic bash completion for PEAR cli script.
 *
 * Based on PEAR 1.9.2.
 */

require_once __DIR__.'/../lib/Cliff.php';
use \cliff\Cliff;

$commands = "
build                  Build an Extension From C Source
bundle                 Unpacks a Pecl Package
channel-add            Add a Channel
channel-alias          Specify an alias to a channel name
channel-delete         Remove a Channel From the List
channel-discover       Initialize a Channel from its server
channel-info           Retrieve Information on a Channel
channel-login          Connects and authenticates to remote channel server
channel-logout         Logs out from the remote channel server
channel-update         Update an Existing Channel
clear-cache            Clear Web Services Cache
config-create          Create a Default configuration file
config-get             Show One Setting
config-help            Show Information About Setting
config-set             Change Setting
config-show            Show All Settings
convert                Convert a package.xml 1.0 to package.xml 2.0 format
cvsdiff                Run a \"cvs diff\" for all files in a package
cvstag                 Set CVS Release Tag
download               Download Package
download-all           Downloads each available package from the default channel
info                   Display information about a package
install                Install Package
list                   List Installed Packages In The Default Channel
list-all               List All Packages
list-channels          List Available Channels
list-files             List Files In Installed Package
list-upgrades          List Available Upgrades
login                  Connects and authenticates to remote server [Deprecated in favor of channel-login]
logout                 Logs out from the remote server [Deprecated in favor of channel-logout]
makerpm                Builds an RPM spec file from a PEAR package
package                Build Package
package-dependencies   Show package dependencies
package-validate       Validate Package Consistency
pickle                 Build PECL Package
remote-info            Information About Remote Packages
remote-list            List Remote Packages
run-scripts            Run Post-Install Scripts bundled with a package
run-tests              Run Regression Tests
search                 Search remote package database
shell-test             Shell Script Test
sign                   Sign a package distribution file
svntag                 Set SVN Release Tag
uninstall              Un-install Package
update-channels        Update the Channel List
upgrade                Upgrade Package
upgrade-all            Upgrade All Packages [Deprecated in favor of calling upgrade with no parameters]
help
";

function get_commands_list()
{
	global $commands;
	$list = array();
	foreach(explode("\n", $commands) as $line)
	{
		list($cmd) = explode(' ', $line, 2);
		if($cmd != '')
			$list[] = trim($cmd);
	}
	return $list;
}

Cliff::run(
	Cliff::config()
	->desc('Bash completion script for PEAR')

	->flag('-v')
	->flag('-q')
	->option('-c') // file    find user configuration in `file'
	->option('-C') // file    find system configuration in `file'
	->option('-d') // foo=bar set user config variable `foo' to `bar'
	->option('-D') // foo=bar set system config variable `foo' to `bar'
	->flag('-G')
	->flag('-s')
	->flag('-S')
	->option('-u') // foo     unset `foo' in the user configuration
	->flag('-h -?')
	->flag('-V')

	->param('command', array(
		'use_for_commands' => true,
		'validator'        => '/./', // we need to supply this so Cliff won't require an actual defined command name
		'completion'       => 'get_commands_list',
	))

	// making help complete subcommand names
	->command('help', Cliff::config()
		->flag('--test')
		->param('cmd', array(
			'completion' => 'get_commands_list',
		))
	)

	// More examples of subcommand competion/configuration below.
	// Unfortunately, the second most useful completion (for `install`)
	// is not possible: list of all packages is very slow and it is not acceptable
	// for a background UI operation such as completion.

	->command('list', Cliff::config()
		->option('--channel')
		->flag('--allchannels')
		->flag('--channelinfo')
	)

	->command('upgrade', Cliff::config()
		->option('--channel')
		->flag('--force')
		->flag('--loose')
		->flag('--nodeps')
		->flag('--register-only')
		->flag('--nobuild')
		->flag('--nocompress')
		->option('--installroot')
		->flag('--ignore-errors')
		->flag('--alldeps')
		->flag('--onlyreqdeps')
		->flag('--offline')
		->flag('--pretend')
	)
);
