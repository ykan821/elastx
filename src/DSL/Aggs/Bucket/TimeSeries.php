<?php

declare(strict_types=1);

namespace ElasticKit\DSL\Aggs\Bucket;

use ElasticKit\DSL\Node;

class TimeSeries extends Node
{
    protected string $_key = 'time_series';

    /**
     * @param string $value
     * @return static
     */
    public function calendarInterval(string $value): static
    {
        return $this->addProperty('calendar_interval', $value);
    }

    /**
     * @param string $value
     * @return static
     */
    public function fixedInterval(string $value): static
    {
        return $this->addProperty('fixed_interval', $value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function missing($value): static
    {
        return $this->addProperty('missing', $value);
    }
}
