<?php

namespace Segment\Controller;

/**
 *
 * @author Michael-Mosher
 */
interface RestRequestGetter
{
    public function getRestRequest($request_method, Segment\utilities\production\Rest $rest) : TestRestRequest;
}
