<?php

namespace Rubix\Boards;

use Countable;

class Bag implements Countable
{
    /**
     * The items in the bag.
     *
     * @var array
     */
    protected $items;

    /**
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Put an item in the bag.
     *
     * @param  mixed  $item
     * @return self
     */
    public function put($item) : Bag
    {
        array_push($this->items, $item);

        return $this;
    }

    /**
     * Fill the bag with items.
     *
     * @param  array  $items
     * @return self
     */
    public function fill(array $items = []) : Bag
    {
        array_merge($this->items, $items);

        return $this;
    }

    /**
     * Shake the bag of items.
     *
     * @return self
     */
    public function shake() : Bag
    {
        shuffle($this->items);

        return $this;
    }

    /**
     * Take an item from the bag.
     *
     * @return mixed
     */
    public function take()
    {
        $this->shake();

        return array_pop($this->items);
    }

    /**
     * Grab many items from the bag.
     *
     * @param  integer  $n
     * @return array
     */
    public function grab(int $n = 1) : array
    {
        $this->shake();

        return array_splice($this->items, -$n, $n);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * Is the bag empty?
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->items);
    }
}
