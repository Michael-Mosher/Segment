<?php

namespace Segment\Controller\production;

/**
 * Cache wrapper that uses the SESSION cache.
 *
 * @author michaelmosher
 */
class SessionCacheWrapper implements \Segment\Controller\CacheWrapper
{
    private $sname = '';
    private $sid = '';
    
    public function __construct(string $session_n, string $session_id)
    {
        $this->sname = $session_n;
        $this->sid = $session_id;
    }

    public function getValue(string $key)
    {
        return \Segment\Controller\production\SessionManager::getValue($key, $this->sname, $this->sid);
    }

    public function isKey(string $key): bool
    {
        return \Segment\Controller\production\SessionManager::isKey($key, $this->sname, $this->sid);
    }

    public function removeKey(string $key): void
    {
        \Segment\Controller\production\SessionManager::removeKey($key, $this->sname, $this->sid);
    }

    public function setValue(string $key, $value): void
    {
        \Segment\Controller\production\SessionManager::setValue($value, $key, $this->sname, $this->sid);
    }

}
