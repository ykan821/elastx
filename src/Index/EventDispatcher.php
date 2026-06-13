<?php

declare(strict_types=1);

namespace ElasticKit\Index;

/**
 * Event dispatcher for index operations.
 */
class EventDispatcher
{
    /**
     * @var array<string, array<int, callable>>
     */
    private static array $listeners = [];

    /**
     * Register an event listener.
     *
     * Supports exact event name, category wildcard (e.g. 'search.*'), or global '*'.
     *
     * @param string $event
     * @param callable $listener receives (Event $event)
     * @return void
     */
    public static function listen(string $event, callable $listener): void
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event to all matching listeners.
     *
     * @param Event $event
     * @return void
     */
    public static function dispatch(Event $event): void
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
    public static function reset(): void
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
    private static function matchesCategory(string $pattern, string $event): bool
    {
        if (substr($pattern, -2) !== '.*') {
            return false;
        }

        $prefix = substr($pattern, 0, -1);
        return strpos($event, $prefix) === 0;
    }
}
