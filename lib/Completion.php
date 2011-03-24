<?

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
	 * @param string $command full command to be completed
	 * @param int $cursor_offset position of the cursor
	 * @return string[]
	 */
	public function complete($command, $cursor_offset)
	{
		$tokenizer = new Tokenizer($command);

		$last_arg = false;
		$words    = array();
		while($next = $tokenizer->read($cursor_offset))
		{
			$last_arg = $next;
			$words[]  = $next['word'];
		}
		array_shift($words); // first one is the executable name

		if(empty($words))
			$last_arg = false;

		try
		{
			$options = $this->config->complete($words, $last_arg ? $last_arg['word'] : '');
		}
		catch(\Exception $e)
		{
			return array();
		}

		return $this->reduce_options($options, $last_arg);
	}

	/**
	 * Tailors list of options to the prefix which is being completed
	 *
	 * @param string[] $options
	 * @param array $arg
	 * @return string[]
	 */
	protected function reduce_options($options, $arg)
	{
		if(!$arg)
			return $options;

		$cmp_word = strtolower($arg['word']);
		$cmp_arg  = strtolower($arg['arg']);
		$length   = strlen($arg['word']);

		foreach($options as $k=>$v)
		{
			// need to convert the casing (ac<tab> -> aCC)
			$prefix = strtolower(substr($v, 0, $length));
			if($prefix == $cmp_word)
			{
				// prefix matches the word, but not the arg: keep the prefix as it was entered
				if($prefix != $cmp_arg)
					$options[$k] = $arg['arg'] . substr($v, $length);
				// if prefix matches both word and arg, we can safely use the full option string,
				// so completed string will have casing from the option, not as entered
			}
			else
			{
				// does not match; skip this option
				unset($options[$k]);
			}
		}

		return $options;
	}
}