<?php

namespace Rubix\Boards;

use Rubix\Engine\Grid;
use Rubix\Engine\Node;
use Rubix\Engine\Trie;
use InvalidArgumentException;
use SplObjectStorage;

class Boggle extends Grid
{
    const SIZE = 4;

    const DICE = [
        ['a', 'a', 'e', 'e', 'g', 'n'], ['e', 'l', 'r', 't', 't', 'y'], ['a', 'o', 'o', 't', 't', 'w'],
        ['a', 'b', 'b', 'j', 'o', 'o'], ['e', 'h', 'r', 't', 'v', 'w'], ['c', 'i', 'm', 'o', 't', 'u'],
        ['d', 'i', 's', 't', 't', 'y'], ['e', 'i', 'o', 's', 's', 't'], ['d', 'e', 'l', 'r', 'v', 'y'],
        ['a', 'c', 'h', 'o', 'p', 's'], ['h', 'i', 'm', 'n', 'q', 'u'], ['e', 'e', 'i', 'n', 's', 'u'],
        ['e', 'e', 'g', 'h', 'n', 'w'], ['a', 'f', 'f', 'k', 'p', 's'], ['h', 'l', 'n', 'n', 'r', 'z'],
        ['d', 'e', 'i', 'l', 'r', 'x'],
    ];

    const DIRECTIONS = [
        [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0], [-1, -1], [0, -1], [1, -1],
    ];

    const SCORING = [
        0 => 0, 1 => 0, 2 => 0, 3 => 1, 4 => 1, 5 => 2, 6 => 3, 7 => 5, 8 => 11,
    ];

    /**
     * A dictionary representing the entire vocabulary of the player.
     *
     * @var \Rubix\Engine\Trie
     */
    protected $dictionary;

    /**
     * @param  array  $words
     * @return void
     */
    public function __construct(array $words = [])
    {
        $this->dictionary = new Trie($words);

        parent::__construct(['row', 'col']);

        foreach (range(1, static::SIZE) as $col) {
            foreach (range(1, static::SIZE) as $row) {
                $this->insert([
                    'col' => $col,
                    'row' => $row,
                    'letter' => null,
                ]);
            }
        }

        foreach ($this->nodes as $node) {
            foreach (static::DIRECTIONS as $direction) {
                $col = $node->col + $direction[0];
                $row = $node->row + $direction[1];

                if ($col < 1 || $col > static::SIZE || $row < 1 || $row > static::SIZE) {
                    continue;
                }

                $neighbor = $this->nodes
                    ->where('col', '==', $col)
                    ->where('row', '==', $row)
                    ->first();

                $node->attach($neighbor);
            }
        }
    }

    /**
     * @return \Rubix\Engine\Trie
     */
    public function dictionary() : Trie
    {
        return $this->dictionary;
    }

    /**
     * Randomize the letters on the board.
     *
     * @return self
     */
    public function shake() : Boggle
    {
        $bag = new Bag();

        foreach (static::DICE as $die) {
            $bag->put(new Dice($die));
        }

        $dice = $bag->grab($this->nodes->count());

        foreach ($this->nodes as $node) {
            $node->set('letter', current($dice)->roll());

            next($dice);
        }

        return $this;
    }

    /**
     * Preset the board using a matrix of predefined letters.
     *
     * @param  array  $letters
     * @return self
     */
    public function preset(array $letters) : Boggle
    {
        $counter = 0;

        if (count($letters, COUNT_RECURSIVE) / 2 !== static::SIZE) {
            throw new InvalidArgumentException('Letter matrix must be the exact size of the board.');
        }

        foreach ($letters as $row) {
            foreach ($row as $letter) {
                $this->find(++$counter)->set('letter', $letter);
            }
        }

        return $this;
    }

    /**
     * Return a text blob representation of the board.
     *
     * @return string
     */
    public function show() : string
    {
        return chunk_split(strtoupper(array_reduce($this->nodes->pluck('letter'), function ($carry, $letter) {
            return $carry .= ' ' . $letter;
        }, '')), static::SIZE * 2);
    }

    /**
     * Play Boggle.
     *
     * @return array
     */
    public function play() : array
    {
        $words = $this->findWords();

        $score = $this->score($words);

        return [
            'words' => $words,
            'score' => $score,
        ];
    }

    /**
     * Traverse the board and return all matching strings.
     *
     * @return  array
     */
    public function findWords() : array
    {
        $discovered = new SplObjectStorage();
        $words = [];

        foreach ($this->nodes as $node) {
            $this->_findWords($node, $this->dictionary->root(), $discovered, '', $words);
        }

        return array_count_values($words);
    }

    /**
     * Recursive backtracking function to traverse board and trie in unison while avoiding
     * dead ends and adding matched strings to the words array along the way.
     *
     * @param  \Rubix\Engine\Node  $root
     * @param  \Rubix\Engine\Node  $prefix
     * @param  \SplObjectStorage  $discovered
     * @param  string  $string
     * @param  array  $words
     * @return void
     */
    protected function _findWords(Node $root, Node $prefix, SplObjectStorage $discovered, string $string, array &$words) : void
    {
        $discovered->attach($root);
        $string .= $root->letter;

        if ($prefix->edges()->has($root->letter)) {
            $prefix = $prefix->edges()->get($root->letter)->node();

            if ($prefix->word) {
                $words[] = $string;
            }

            foreach ($root->edges() as $edge) {
                $node = $edge->node();

                if (!$discovered->contains($node)) {
                    if ($prefix->edges()->has($node->letter)) {
                        $this->_findWords($node, $prefix, $discovered, $string, $words);
                    }
                }
            }
        }

        $discovered->detach($root);
        $string = substr($string, 0, -1);
        $prefix = $prefix->parent;
    }

    /**
     * Calculate the score of the result of a game.
     *
     * @param  array  $result
     * @return int
     */
    public function score(array $words) : int
    {
        $score = 0;

        foreach ($words as $word => $frequency) {
            $length = strlen($word);

            if ($length > 8) {
                $score += static::SCORING[8] * $frequency;
            } else {
                $score += static::SCORING[$length] * $frequency;
            }
        }

        return $score;
    }
}
