<?php

namespace Rubix\Boards;

use InvalidArgumentException;

class Dice
{
    /**
     * An array holding each side of the die.
     *
     * @var array
     */
    protected $sides;

    /**
     * @param  array  $sides
     * @return void
     */
    public function __construct(array $sides = [1, 2, 3, 4, 5, 6])
    {
        if (count($sides) < 1) {
            throw new InvalidArgumentException('Die cannot have less than 1 side.');
        }

        $this->sides = $sides;
    }

    /**
     * Roll the die and return the result.
     *
     * @return mixed
     */
    public function roll()
    {
        return $this->sides[array_rand($this->sides)];
    }
}
