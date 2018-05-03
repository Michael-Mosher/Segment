<?php

namespace Segment\Controller;

/**
 * Decorator-pattern extension of \Segment\Controller\ModelCallOrchestrator
 * @author Michael-Mosher
 */
interface ModelCallOrchestratorCacheDecorator extends ModelCallOrchestrator
{
    /**
     * Constructor
     * @param \Segment\Controller\RestRequest $request
     * @param \Segment\utilities\DbDescription $db_descrip
     * @param \Segment\Model\production\ModelCaller $mc
     * @param \Closure $options_arg_getter_func
     * @param \Segment\Controller\CacheWrapper $cache_handler
     * @param \Segment\Controller\ModelCallOrchestrator $decoration
     */
    public function __construct(
            \Segment\Controller\RestRequest $request, \Segment\utilities\DbDescription $db_descrip,
            \Segment\Model\production\ModelCaller &$mc, \Closure $options_arg_getter_func,
            \Segment\Controller\CacheWrapper &$cache_handler,
            ModelCallOrchestrator $decoration = NULL);
}
