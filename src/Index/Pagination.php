<?php

namespace ElasticKit\Index;

/**
 * Manages pagination resolvers.
 */
class Pagination
{
    /**
     * @var callable|null
     */
    private static $pageResolver;

    /**
     * @var callable|null
     */
    private static $paginatorResolver;

    /**
     * Register a resolver that extracts page and perPage from the request.
     *
     * @param callable $resolver returns [$page, $perPage]
     * @return void
     */
    public static function setPageResolver(callable $resolver)
    {
        self::$pageResolver = $resolver;
    }

    /**
     * Return the registered page resolver, or null.
     *
     * @return callable|null
     */
    public static function getPageResolver()
    {
        return self::$pageResolver;
    }

    /**
     * Register a resolver that converts Results into a framework paginator.
     *
     * @param callable $resolver receives (Results $results, int $page, int $perPage)
     * @return void
     */
    public static function setPaginatorResolver(callable $resolver)
    {
        self::$paginatorResolver = $resolver;
    }

    /**
     * Return the registered paginator resolver, or null.
     *
     * @return callable|null
     */
    public static function getPaginatorResolver()
    {
        return self::$paginatorResolver;
    }

    /**
     * Reset all resolvers. Mainly for testing.
     *
     * @return void
     */
    public static function reset()
    {
        self::$pageResolver = null;
        self::$paginatorResolver = null;
    }
}
