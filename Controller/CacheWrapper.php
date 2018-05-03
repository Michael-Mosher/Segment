<?php

namespace Segment\Controller;

/**
 *
 * @author michaelmosher
 */
interface CacheWrapper
{
    
    public function getValue(string $key):mixed;
    
    
    public function isKey(string $key):bool;
    
    
    public function setValue(string $key, $value): void;
    
    
    public function removeKey(string $key): void;
}
