<?php

namespace Rubix\Boards;

use Rubix\Engine\Grid;
use Rubix\Engine\Node;
use Rubix\Engine\Trie;

class Scrabble extends Grid
{
    const WIDTH = 15;
    const HEIGHT = 15;

    const TILES = [
        'a' => 9, 'b' => 2, 'c' => 2, 'd' => 4, 'e' => 12, 'f' => 2, 'g' => 3, 'h' => 2,
        'i' => 9, 'j' => 1, 'k' => 1, 'l' => 4, 'm' => 3, 'n' => 6, 'o' => 8, 'p' => 2,
        'q' => 1, 'r' => 6, 's' => 4, 't' => 6, 'u' => 4, 'v' => 2, 'w' => 2, 'x' => 1,
        'y' => 2, 'z' => 1,
    ];

    const START_POSITION = [8, 8];

    const DOUBLE_LETTER = [
        [4, 1], [1, 4], [12, 1], [1, 12], [4, 15], [12, 15], [15, 4], [15, 12],
        [3, 7], [4, 8], [3, 9], [7, 3], [8, 4], [9, 3], [7, 13], [8, 12], [9, 13],
        [13, 7], [12, 8], [13, 9], [7, 7], [9, 9], [7, 9], [9, 7],
    ];

    const TRIPLE_LETTER = [
        [6, 2], [10, 2], [2, 6], [2, 10], [6, 14], [10, 14], [14, 6], [14, 10],
        [6, 6], [10, 10], [6, 10], [10, 6],
    ];

    const DOUBLE_WORD = [
        [2, 2], [3, 3], [4, 4], [5, 5], [11, 5], [12, 4], [13, 3], [14, 2],
        [14, 2], [13, 3], [12, 4], [11, 5], [11, 11], [12, 12], [13, 13], [14, 14],
    ];

    const TRIPLE_WORD = [
        [1, 1], [8, 1], [15, 1], [1, 8], [15, 8], [1, 15], [8, 15], [15, 15],
    ];

    const SCORING = [
        'a' => 1, 'b' => 3, 'c' => 3, 'd' => 2, 'e' => 1, 'f' => 4, 'g' => 2, 'h' => 4,
        'i' => 1, 'j' => 8, 'k' => 5, 'l' => 1, 'm' => 2, 'n' => 1, 'o' => 1, 'p' => 3,
        'q' => 10, 'r' => 1, 's' => 1, 't' => 1, 'u' => 1, 'v' => 4, 'w' => 4, 'x' => 8,
        'y' => 4, 'z' => 10,
    ];

    const DIRECTIONS = [
        [1, 0], [0, 1], [-1, 0], [0, -1],
    ];

    const MAX_TILES_IN_HAND = 7;

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

        foreach (range(1, static::HEIGHT) as $col) {
            foreach (range(1, static::WIDTH) as $row) {
                $this->insert($this->counter->next(), [
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

                if ($col < 1 || $col > static::HEIGHT || $row < 1 || $row > static::WIDTH) {
                    continue;
                }

                $neighbor = $this->nodes()
                    ->where('col', '==', $col)
                    ->where('row', '==', $row)
                    ->first();

                $node->attach($neighbor);
            }
        }
    }

    /**
     * Play Scrabble.
     *
     * @return array
     */
    public function play() : array
    {
        $bag = new Bag();
        $hand = [];
        $words = [];
        $score = 0;

        foreach (static::TILES as $letter => $frequency) {
            foreach (range(1, $frequency) as $i) {
                $bag->put($letter);
            }
        }

        while (!$bag->isEmpty()) {
            $hand = array_merge($hand, $bag->grab(self::MAX_TILES_IN_HAND - count($hand)));

            $result = $this->findBestWord($hand);

            if (strlen($result['word']) > 0) {
                $words[] = $result['word'];
                $score += $result['score'];
            } else {
                break;
            }

            $hand = array_diff($hand, str_split($result['word']));
        }

        return [
            'words' => $words,
            'score' => $score,
        ];
    }

    /**
     * Find the highest scoring word from a given set of letters starting with a
     * given prefix.
     *
     * @param  array  $letters
     * @param  string  $before
     * @return array
     */
    public function findBestWord(array $letters, string $before = '', int $max = 7) : array
    {
        $prefix = $this->dictionary->find($before);
        $word = '';
        $score = 0;

        foreach (range(0, $max) as $k) {
            $this->_findBestWord($letters, $before, $prefix, '', count($letters), $k, $word, $score);
        }

        return [
            'word' => $word,
            'score' => $score,
        ];
    }

    /**
     * Recursive function to permute all given letters of count n into strings of length k
     * and return the word with the highest possible score.
     *
     * @param  array  $letters
     * @param  string  $before
     * @param  \Rubix\Boards\Node  $prefix
     * @param  string  $string
     * @param  int  $n
     * @param  int  $k
     * @param  string  $word
     * @param  int  $best
     */
    protected function _findBestWord(array $letters, string $before, Node $prefix, string $string, int $n, int $k, string &$word, int &$score)
    {
        if ($k === 0) {
            $result = $before . $string;

            if (array_key_exists($result, $prefix->get('words', []))) {
                $temp = $this->score($result);

                if ($temp > $score) {
                    $word = $result;
                    $score = $temp;
                }
            }

            return;
        }

        foreach (range(0, $n - 1) as $i) {
            if ($prefix->edges()->has($letters[$i])) {
                $prefix = $prefix->edges()->get($letters[$i])->node();
                $string .= $letters[$i];

                $this->_findBestWord($letters, $before, $prefix, $string, $n, $k - 1, $word, $score);

                $string = substr($string, 0, -1);
                $prefix = $prefix->parent;
            }
        }
    }

    /**
     * Calculate the score of the result of a game.
     *
     * @param  string  $word
     * @return int
     */
    public function score(string $word) : int
    {
        $score = 0;

        foreach (str_split($word) as $letter) {
            $score += self::SCORING[$letter];
        }

        return $score;
    }
}
