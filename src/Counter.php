<?php

namespace Rubix\Engine;

class Counter
{
    /**
     * The auto incrementing ID value.
     *
     * @var int
     */
    protected $autoincrement;

    /**
     * @param  int  $offset
     * @return void
     */
    public function __construct(int $offset = 1)
    {
        $this->autoincrement = $offset;
    }

    /**
     * Return the current ID.
     *
     * @return int
     */
    public function current() : int
    {
        return $this->autoincrement;
    }

    /**
     * Return the next ID and increment the counter.
     *
     * @return int
     */
    public function next() : int
    {
        return $this->autoincrement++;
    }
}
