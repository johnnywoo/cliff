<?php

namespace Cliff;

require_once __DIR__ . '/Token.php';

/**
 * Reads args (words) from raw command line string (bash shell syntax)
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
     * @param int $stopAtOffset
     * @return Token
     */
    public function read($stopAtOffset = -1)
    {
        if (!strlen($this->string) || ($stopAtOffset > -1 && $this->offset >= $stopAtOffset)) {
            return false;
        }

        $arg  = '';
        $word = '';

        $isHeadingWhitespace = true;
        $openQuote            = '';

        while (strlen($this->string)) {
            if ($this->offset == $stopAtOffset) {
                break;
            }

            $c = substr($this->string, 0, 1);

            $stepLength = 1;
            $skipChar   = false;

            switch ($c) {
                case ' ':
                    if ($isHeadingWhitespace) {
                        $skipChar = true; // ignore it, continue reading
                        break;
                    }

                    if ($openQuote) {
                        $word .= $c;
                        break;
                    }

                    break 2; // the arg is finished, stop reading (the space char remains in the string)

                case '"':
                case "'":
                    $isHeadingWhitespace = false;

                    if ($openQuote == $c) {
                        $openQuote = ''; // quote closed
                    } elseif ($openQuote) {
                        $word .= $c; // other quote inside: "it's"
                    } else {
                        $openQuote = $c; // quote start
                    }
                    break;

                case '\\':
                    $isHeadingWhitespace = false;

                    $nextChar = substr($this->string, 1, 1);

                    // if we should stop at the next char, the slash cannot escape
                    // anything and should be treated as a regular char
                    if ($this->offset + 1 == $stopAtOffset) {
                        $word .= $c;
                        break;
                    }

                    // in single quotes backslash does not work (treated as a regular char)

                    // outside quotes slash is stripped from any char, except for the newline
                    if (!$openQuote && $nextChar != "\n") {
                        $word .= $nextChar;
                        $stepLength++;
                        break;
                    }

                    // in double quotes and outside backslash escapes itself and newline
                    if (!$openQuote || $openQuote == '"') {
                        if ($nextChar == '\\') {
                            // word receives one slash
                            $word .= $nextChar;
                            $stepLength++;
                            break;
                        }

                        if ($nextChar == "\n") {
                            // word receives nothing, the newline gets eaten
                            $stepLength++;
                            break;
                        }
                    }

                    // in double quotes some extra chars can be escaped
                    if ($openQuote == '"' && $nextChar != '' && strstr('"`$', $nextChar)) {
                        $word .= $nextChar;
                        $stepLength++;
                        break;
                    }

                    $word .= $c;
                    break;

                default:
                    $isHeadingWhitespace = false;
                    $word .= $c;
            }

            if (!$skipChar) {
                $arg .= substr($this->string, 0, $stepLength);
            }

            $this->string = substr($this->string, $stepLength);
            $this->offset += $stepLength;
        }

        return new Token($arg, $word);
    }
}
