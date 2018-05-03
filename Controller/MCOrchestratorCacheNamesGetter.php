<?php

namespace Segment\Controller;

/**
 *
 * @author michaelmosher
 */
interface MCOrchestratorCacheNamesGetter {
    /**
     * Returns the names of those Model Call Observers for this client function, if any.
     * @param string $rest_func
     * @param string $rest_type The request type (e.g. GET, POST, PUT, DELETE).
     * @return array An array of class name strings. The array size may be zero.
     */
    public static function getMCOrchestratorCacheNames(string $rest_func, string $rest_type):array;
}
