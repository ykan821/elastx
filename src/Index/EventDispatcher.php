<?php

namespace ElasticKit\Index;

/**
 * Event dispatcher for index operations.
 */
class EventDispatcher
{
    /**
     * @var array<string, array<int, callable>>
     */
    private static $listeners = [];

    /**
     * Register an event listener.
     *
     * Supports exact event name, category wildcard (e.g. 'search.*'), or global '*'.
     *
     * @param string $event
     * @param callable $listener receives (Event $event)
     * @return void
     */
    public static function listen($event, callable $listener)
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event to all matching listeners.
     *
     * @param Event $event
     * @return void
     */
    public static function dispatch(Event $event)
    {
        foreach (self::$listeners as $pattern => $listeners) {
            if ($pattern === $event->name || $pattern === '*' || self::matchesCategory($pattern, $event->name)) {
                foreach ($listeners as $listener) {
                    $listener($event);
                }
            }
        }
    }

    /**
     * Reset all registered listeners. Mainly for testing.
     *
     * @return void
     */
    public static function reset()
    {
        self::$listeners = [];
    }

    /**
     * Check if a wildcard pattern matches an event by category.
     *
     * @param string $pattern
     * @param string $event
     * @return bool
     */
    private static function matchesCategory($pattern, $event)
    {
        if (substr($pattern, -2) !== '.*') {
            return false;
        }

        $prefix = substr($pattern, 0, -1);
        return strpos($event, $prefix) === 0;
    }
}
