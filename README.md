# Cliff — a CLI framework for PHP

Cliff allows you to write CLI scripts in PHP with comfort.
The main features are:

  * Arguments/options parser
  * Terse chained-call configuration syntax
  * No-config mode
  * Usage and help generation
  * Validation and callbacks for args
  * Exception handling

The basic idea of Cliff is to allow you to work with CLI arguments exactly the same way
you work with http request variables: by using `$_REQUEST`. All you need is to describe
options and parameters of your script, and then everything just works. In no-config mode,
you don't event have to describe anything.

## HOWTO

You may want to look at scripts in `examples` directory, or even simply grab one of those
and modify to your needs.

A short example:

    <?php
    require_once 'cliff/lib/Cliff.php';
    use \cliff\Cliff;

    Cliff::run(Cliff::config()->many_params('lines'));

    foreach($_REQUEST['lines'] as $arg)
    {
        echo "$arg\n";
    }
    ?>

This little script will take arbitrary number of arguments and output each of them
on a separate line. Now, if you want to add an option to uppercase the arguments
before outputting them, it can be done quite easily:

    <?php
    require_once 'cliff/lib/Cliff.php';
    use \cliff\Cliff;

    Cliff::run(
        Cliff::config()
        ->flag('--uppercase -u') // adds --uppercase and -u as its alias
        ->many_params('lines')
    );

    foreach($_REQUEST['lines'] as $arg)
    {
        if($_REQUEST['uppercase'])    // this will be FALSE if the flag is not set,
            $arg = strtoupper($arg);  // so you don't have to worry about notices

        echo "$arg\n";
    }
    ?>

Now our script may be called as `php script.php -u abc def`. If called without arguments
or with incorrect ones, the script will show a short description of available options and
parameters. 'Parameter' in Cliff means any non-option argument.

Read phpdoc comments in `lib/Config.php` for all configuration possibilities.

## NO-CONFIG MODE

No-config mode allows you to have your cake and eat it too: you get $_REQUEST filling
for easy work, but don't have to configure anything.

    <?php
    require_once 'cliff/lib/Cliff.php';
    \cliff\Cliff::run();

    // work with $_REQUEST as you like:
    // -x will become $_REQUEST['x'] == true
    // --option will become $_REQUEST['option'] == true
    // --option=a will become $_REQUEST['option'] == 'a'
    // non-options will be accumulated in $_REQUEST['args'] array

You can always add configuration later, to enable proper usage, validation and bash completion.

## NOTES

To change error exit code (which is used when an uncaught exception occurs), you need to
change Cliff::$error_exit_code. Default error exit code is 1.

You can use Cliff to add bash completion to any other program. Simply create a cliff script
with nothing but config and completion callbacks, and set the actual program name as alias
when installing the completion into bash profile.

## REQUIREMENTS

The only thing Cliff requires is PHP 5.3.

## TODO

If you don't mind, I'll leave this todo list here.

  * [+] Modifying option values and params via validator callbacks
  * [+] Special class for argument-related exceptions, so we can show usage only for those
  * [+] Specifying default error exit code somewhere (for grep-like exit codes)
  * [+] Separate options and flags
  * [+] A way to specify required options and options that require a value but provide default one
  * [+] Ability to allow unconfigured options
  * [+] Non-config mode: aggregate all provided options and store all params into $_REQUEST['args'],
    no validation or anything; could be useful for tmp scripts (hack a tool together with no design
    planning and then, when it matures, configure it for usage and completion)
  * [+] A way to specify optional parameters (e.g. vmig db [table])
  * Ability to specify default values for single-letter options
  * Allow a string instead of $props array
  * Bash completion for options and params
    * Aliases when installing completion into bash profile (like g='script' x='script -a -b')
  * Helper for colors
  * Helper for reading (char, string, password, stdin)
  * Helper for writing (out, err, interface = skipped if not tty, table)
  * Ability to store STDIN in a variable (is it possible to iterate over it instead?)
  * Ability to design complex subcommand structures via branches and subcommands
    (to distinguish branches we can just forbid two consequent branches with no params)
    * Adopting external commands as subcommands
    * Adopting external Cliff scripts as subcommands with usage/completion import
      * Load config from Cliff XML (not sure if PEAR::Console_CommandLine format is enough)
      * Generate config in Cliff XML
  * Load config from PEAR::Console_CommandLine XML
