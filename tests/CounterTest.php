<?php

use Rubix\Boards\Counter;
use PHPUnit\Framework\TestCase;

class CounterTest extends TestCase
{
    public function setUp()
    {
        //
    }

    public function test_increment_id()
    {
        $counter = new Counter();

        $this->assertTrue($counter instanceof Counter);
        $this->assertEquals(1, $counter->next());
        $this->assertEquals(2, $counter->next());
        $this->assertEquals(3, $counter->next());
        $this->assertEquals(4, $counter->next());
    }
}
