<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Segment\Controller;

/**
 *
 * @author Michael-Mosher
 */
interface CacheFunctionDecorator extends RestFunctionDecorator
{
    public function __construct(RestRequest $rr, RestFunction $decoration = NULL, CacheWrapper $cache = NULL);
}
