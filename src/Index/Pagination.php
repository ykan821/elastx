<?php

declare(strict_types=1);

namespace ElasticKit\Index;

/**
 * Manages pagination resolvers.
 */
class Pagination
{
    /**
     * @var callable|null
     */
    private static $pageResolver = null;

    /**
     * @var callable|null
     */
    private static $paginatorResolver = null;

    /**
     * Register a resolver that extracts page and perPage from the request.
     *
     * @param callable $resolver returns [$page, $perPage]
     * @return void
     */
    public static function setPageResolver(callable $resolver): void
    {
        self::$pageResolver = $resolver;
    }

    /**
     * Return the registered page resolver, or null.
     *
     * @return callable|null
     */
    public static function getPageResolver(): ?callable
    {
        return self::$pageResolver;
    }

    /**
     * Register a resolver that converts Results into a framework paginator.
     *
     * @param callable $resolver receives (Results $results, int $page, int $perPage)
     * @return void
     */
    public static function setPaginatorResolver(callable $resolver): void
    {
        self::$paginatorResolver = $resolver;
    }

    /**
     * Return the registered paginator resolver, or null.
     *
     * @return callable|null
     */
    public static function getPaginatorResolver(): ?callable
    {
        return self::$paginatorResolver;
    }

    /**
     * Reset all resolvers. Mainly for testing.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$pageResolver = null;
        self::$paginatorResolver = null;
    }
}
