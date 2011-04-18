<?

namespace cliff;

class Token
{
	/**
	 * The arg in raw form, as it was written in the command
	 * @var string
	 */
	public $arg = '';

	/**
	 * The word that is parsed out of the arg
	 * @var string
	 */
	public $word = '';

	public function __construct($arg, $word)
	{
		$this->arg  = $arg;
		$this->word = $word;
	}

	public function get_last_word($wordbreaks = " \t\n")
	{
		$wb = preg_quote($wordbreaks);
		if(preg_match('{['.$wb.']([^'.$wb.']*)$}', $this->arg, $m))
			return $m[1];

		return $this->arg;
	}
}