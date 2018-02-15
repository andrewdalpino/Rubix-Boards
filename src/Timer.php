<?php

namespace Rubix\Boards;

use Rubix\Engine\Stats;

class Timer
{
    /**
     * The start time.
     *
     * @var float
     */
    protected $start;

    /**
     * The stop time.
     *
     * @var float
     */
    protected $stop;

    /**
     * The time at when the timer is expired.
     *
     * @var  float
     */
    protected $expired;

    /**
     * The number of interval to put on the timer.
     *
     * @var float
     */
    protected $interval;

    /**
     * The number of decimal places to keep track of.
     *
     * @var int
     */
    protected $precision;

    /**
     * @param  float  $interval
     * @param  int  $precision
     * @return void
     */
    public function __construct(float $interval = INF, int $precision = 5)
    {
        $this->interval = $interval;
        $this->precision = $precision;
        $this->reset();
    }

    /**
     * Start the timer.
     *
     * @return self
     */
    public function start() : Timer
    {
        $this->start = $this->now();
        $this->expired = $this->start + $this->interval;
        $this->interval = 0;

        return $this;
    }

    /**
     * Sample the timer at the time the function is called.
     *
     * @return float
     */
    public function sample() : float
    {
        return (float) $this->start ? Stats::round($this->now() - $this->start, $this->precision) : 0;
    }

    /**
     * Pause the timer.
     *
     * @return self
     */
    public function pause() : Timer
    {
        $this->interval = $this->expired - $this->sample();

        return $this;
    }

    /**
     * Stop the timer and return the result.
     *
     * @return self
     */
    public function stop() : Timer
    {
        $this->stop = $this->now();

        return $this;
    }

    /**
     * Return the timer result.
     *
     * @return float
     */
    public function result() : float
    {
        return (float) Stats::round($this->stop - $this->start, $this->precision);
    }

    /**
     * Set the timer interval.
     *
     * @param  float  $interval
     * @return self
     */
    public function setInterval(float $interval) : Timer
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Return the current tiem in microinterval.
     *
     * @return float
     */
    public function now() : float
    {
        return microtime(true);
    }

    /**
     * Reset the timer.
     *
     * @return self
     */
    public function reset() : Timer
    {
        $this->start = 0;
        $this->stop = 0;
        $this->expired = null;

        return $this;
    }

    /**
     * Is the timer still valid?
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->now() < $this->expired;
    }
}
