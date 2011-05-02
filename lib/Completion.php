<?

/*

DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

Cliff: a CLI framework for PHP.
Copyright 2011 Aleksandr Galkin.

This file is part of Cliff.

Cliff is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

Cliff is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Cliff. If not, see <http://www.gnu.org/licenses/>.

*/

namespace cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__.'/Tokenizer.php';

class Completion
{
	/** @var Config */
	private $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Calculates the completion options
	 *
	 * For meaning of COMP_WORDBREAKS refer to bash manual.
	 *
	 * @param string $command full command to be completed
	 * @param int $cursor_offset position of the cursor
	 * @param string $comp_wordbreaks
	 * @return string[]
	 */
	public function complete($command, $cursor_offset, $comp_wordbreaks = " \t\n\"'><=;|&(:")
	{
		$tokenizer = new Tokenizer($command);

		$last_arg = false;
		$words    = array();
		while($next = $tokenizer->read($cursor_offset))
		{
			$last_arg = $next;
			$words[]  = $next->word;
		}
		array_shift($words); // first one is the executable name

		if(empty($words))
			$last_arg = false;

		try
		{
			$options = $this->config->complete($words, $last_arg ? $last_arg->word : '');
			return $this->reduce_options($options, $last_arg, $comp_wordbreaks);
		}
		catch(\Exception $e)
		{
			return array();
		}
	}

	/**
	 * Tailors list of options to the prefix which is being completed
	 *
	 * @param string[] $options
	 * @param Token $arg
	 * @param string $comp_wordbreaks
	 * @return string[]
	 */
	protected function reduce_options($options, $arg, $comp_wordbreaks)
	{
		if(!$arg)
			return $options;

		$cmp_word = strtolower($arg->word);
		$cmp_arg  = strtolower($arg->arg);
		$length   = strlen($cmp_arg);

		// Readline treats our completion options as variants of last comp-word.
		// Those words are separated not by IFS, like shell-words, but by
		// COMP_WORDBREAKS characters like '=' and ':'.
		//
		// Our tokenizer splits its words in a shell-word manner, therefore
		// the completion options can contain many comp-words. For correct completion
		// to work, we need to find the last wordbreak and remove everything before it
		// from our options, leaving only the last comp-word.
		$prefix = $arg->get_last_word($comp_wordbreaks);
		$force_prefix = ($prefix != $arg->arg);

		foreach($options as $k=>$variant)
		{
			// need to convert the casing (ac<tab> -> aCC)
			$variant_prefix = strtolower(substr($variant, 0, $length));
			if($variant_prefix != $cmp_word || strlen($variant) == $length)
			{
				// does not match or is equal to what is being completed; skip this option
				unset($options[$k]);
				continue;
			}

			// If the arg matches the word (that is, there is no special syntax in the arg)
			// and we don't have to force the prefix because of a wordbreak, then it's better
			// to use the whole variant string instead of a prefixed one (this way we can
			// get correct case of chars)
			if($force_prefix || $variant_prefix != $cmp_arg)
				$options[$k] = $prefix . substr($variant, $length);
		}

		return $options;
	}

	public static function action_complete(Config $config, $args = null)
	{
		if(is_null($args))
			$args = $_SERVER['argv'];

		$comp_wordbreaks = end($args);
		$comp_point      = prev($args);
		$comp_line       = prev($args);

		/** @var $cmp completion */
		$cmp = new static($config);
		foreach($cmp->complete($comp_line, $comp_point, $comp_wordbreaks) as $opt)
		{
			echo "$opt\n";
		}
	}

	public static function action_bash_profile($alias)
	{
		$fname = realpath($_SERVER['PHP_SELF']);

		// if the file has a shebang, we assume it can execute itself
		if(is_readable($fname) && file_get_contents($fname, 0, null, 0, 2) == '#!')
		{
			$alias_cmd    = $fname;
			$complete_cmd = escapeshellarg($fname);
		}
		else
		{
			$alias_cmd    = 'php ' . escapeshellarg($fname);
			$complete_cmd = $alias_cmd;
		}

		$funcname = '_cliff_complete_' . $alias;

		echo 'alias ' . escapeshellarg($alias) . '=' . escapeshellarg($alias_cmd) . "\n";
		echo 'function ' . $funcname . '() {' . "\n";
		echo '    saveIFS=$IFS' . "\n";
		echo "    IFS=$'\\n'\n";
		echo '    COMPREPLY=($(' . $complete_cmd . ' --cliff-complete-- "$COMP_LINE" "$COMP_POINT" "$COMP_WORDBREAKS"))' . "\n";
		echo '    IFS=$saveIFS' . "\n";
		echo "}\n";
		echo 'complete -o bashdefault -o default -o nospace -F ' . $funcname . ' ' . escapeshellarg($alias) . "\n";
	}
}