<?

namespace cliff;

/**
 * Reads args (words) from raw command line string (bash shell syntax)
 *
 * Todo (maybe): comments, $'c-quotes'
 */
class Tokenizer
{
	private $string = '';
	private $offset = 0;

	public function __construct($string)
	{
		$this->string = $string;
	}

	/**
	 * Fetches next arg from the string
	 *
	 * Returned array keys:
	 * * arg   the raw argument
	 * * word  what would be in $argv (decoded quotes, escaping, etc)
	 *
	 * When there is nothing to read, FALSE is returned.
	 *
	 * @return array
	 */
	public function read($stop_at_offset = -1)
	{
		if(!strlen($this->string) || ($stop_at_offset > -1 && $this->offset >= $stop_at_offset))
			return false;

		$arg  = '';
		$word = '';

		$is_heading_whitespace = true;
		$open_quote = '';

		while(strlen($this->string))
		{
			if($this->offset == $stop_at_offset)
				break;

			$c = substr($this->string, 0, 1);
			$step_length = 1;
			$skip_char = false;

			switch($c)
			{
				case ' ':
					if($is_heading_whitespace)
					{
						$skip_char = true; // ignore it, continue reading
						break;
					}

					if($open_quote)
					{
						$word .= $c;
						break;
					}

					break 2; // the arg is finished, stop reading (the space char remains in the string)

				case '"':
				case "'":
					$is_heading_whitespace = false;

					if($open_quote == $c)
						$open_quote = ''; // quote closed
					else if($open_quote)
						$word .= $c; // other quote inside: "it's"
					else
						$open_quote = $c; // quote start

					break;

				case '\\':
					$is_heading_whitespace = false;

					$next_char = substr($this->string, 1, 1);

					// if we should stop at the next char, the slash cannot escape
					// anything and should be treated as a regular char
					if($this->offset + 1 == $stop_at_offset)
					{
						$word .= $c;
						break;
					}

					// in single quotes backslash does not work (treated as a regular char)

					// outside quotes slash is stripped from any char, except for the newline
					if(!$open_quote && $next_char != "\n")
					{
						$word .= $next_char;
						$step_length++;
						break;
					}

					// in double quotes and outside backslash escapes itself and newline
					if(!$open_quote || $open_quote == '"')
					{
						if($next_char == '\\')
						{
							// word receives one slash
							$word .= $next_char;
							$step_length++;
							break;
						}

						if($next_char == "\n")
						{
							// word receives nothing, the newline gets eaten
							$step_length++;
							break;
						}
					}

					// in double quotes some extra chars can be escaped
					if($open_quote == '"' && $next_char != '' && strstr('"`$', $next_char))
					{
						$word .= $next_char;
						$step_length++;
						break;
					}

					$word .= $c;
					break;

				default:
					$is_heading_whitespace = false;
					$word .= $c;
			}

			if(!$skip_char)
				$arg .= substr($this->string, 0, $step_length);

			$this->string = substr($this->string, $step_length);
			$this->offset += $step_length;
		}

		return array(
			'arg'  => $arg,
			'word' => $word,
		);
	}
}