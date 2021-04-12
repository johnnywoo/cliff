<?php

namespace Cliff;

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

    /**
     * Retrieves a part of the arg according to given wordbreaks
     *
     * Completion engine (at least in bash) uses different wordbreaks than the shell.
     * In shell 'a:b' is (usually) one word, while compwords are 'a' and 'b' here.
     * To make correct prefix for completion results (e.g. '//domain' instead of 'http://domain'),
     * we need to split the arg the way completion wants it.
     *
     * Google 'bash IFS' and 'COMP_WORDBREAKS' for the rest of the lore.
     *
     * @param string $wordbreaks
     * @return string
     */
    public function getArgTail($wordbreaks = " \t\n")
    {
        $wb = preg_quote($wordbreaks);
        if (preg_match('{[' . $wb . ']([^' . $wb . ']*)$}', $this->arg, $m)) {
            return $m[1];
        }

        return $this->arg;
    }
}
