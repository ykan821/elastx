<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class TimeSeries extends Node
{
    protected string $_key = 'time_series';

    /**
     * @param string $calendarInterval
     * @return static
     */
    public function calendarInterval(string $calendarInterval): static
    {
        return $this->addProperty('calendar_interval', $calendarInterval);
    }

    /**
     * @param string $fixedInterval
     * @return static
     */
    public function fixedInterval(string $fixedInterval): static
    {
        return $this->addProperty('fixed_interval', $fixedInterval);
    }

    /**
     * @param mixed $missing
     * @return static
     */
    public function missing($missing): static
    {
        return $this->addProperty('missing', $missing);
    }
}
