<?php
namespace Segment\Controller\production;
class SessionManager
{
    /**
     * Starts, or continues, the client's session as needed. Returns the session ID.
     * @param string $name The client's name
     * @param int $limit The time limit, in seconds.
     * @param string $path The path from $domain to the least specific location the session is valid.
     * @param string $domain The URL domain for cookie.
     * @param bool $secure True if HTTPS, false if HTTP.
    **/
    static function startSession(string $name, int $limit, string $domain, string $path = '/', bool $secure = FALSE) : string
    {
        $continue_private = FALSE;
        // Set the cookie name before we start.
        $name = strlen($name)<1 ? "Session" : $name;
        session_name($name);

        // Set the domain to default to the current domain.
        $domain = strlen($domain)>0 ? $domain : "." . substr(
                __DOMAIN_SANS_WWW__,
                8,
                strpos(__DOMAIN_SANS_WWW__, "/", 8)
                );
        $limit = $limit<0 ? __SESSION_EXPIRATION__ : $limit;
        $path = strlen($path)>0 && strcmp($path[0], "/")===0
                ? $path : "/";
        // Set the default secure value to whether the site is being accessed with SSL
        $https = $secure ? $secure : isset($_SERVER['HTTPS']);

        if(isset($_COOKIE["PHPSESSID"]) || isset($_SERVER["QUERY_STRING"])){
            $rest = new \Segment\utilities\production\Rest($_SERVER["QUERY_STRING"]);
            $private_id = $_COOKIE["PHPSESSID"] ?? $rest->getValue("session_id");
            $continue_private = TRUE;
        }
        // Set the cookie settings and start the session
        if(!$continue_private){
            $private_id = session_id();
        } else {
            session_id($private_id);
        }
        session_set_cookie_params($limit, $path, $domain, $secure, TRUE);
        session_start([
                    'cookie_lifetime' => __SESSION_EXPIRATION__,
                    'use_only_cookies' => TRUE,
                    'cookie_httponly' => TRUE,
                    'read_and_close' => TRUE
                ]);

        // Ensure session hasn't expired. Destroy if so.
        if(self::isFreshSession()){

            if(!self::isValidSession()){
                $_SESSION = array();
                session_start([
                    'cookie_lifetime' => __SESSION_EXPIRATION__,
                    'use_only_cookies' => TRUE,
                    'cookie_httponly' => TRUE
                ]);
                $_SESSION['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
                session_write_close();
            } else if(rand(1, 100)<=5){
                self::regenerateSession();
            }
        } else {
            $_SESSION = array();
            session_destroy();
            $private_id = session_start([
                    'cookie_lifetime' => __SESSION_EXPIRATION__,
                    'use_only_cookies' => TRUE,
                    'cookie_httponly' => TRUE
                ]);
            $_SESSION['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            session_write_close();
        }
        
        foreach ($_SESSION as $key => $value) {
            error_log(__METHOD__ . " at the end the contents of SESSION: {$key} => {$value}");
        }
        foreach ($_COOKIE as $key => $value) {
            error_log(__METHOD__ . " at the end the contents of COOKIE: {$key} => {$value}");
        }
        return $private_id;
   }
   
    /**
     * Every time you call session_start(), PHP adds another
     * identical session cookie to the response header. Do this
     * enough times, and your response header becomes big enough
     * to choke the web server.
     *
     * This method clears out the duplicate session cookies. You can
     * call it after each time you've called session_start(), or call it
     * just before you send your headers.
     * @author aaronw at catalyst dot net dot nz http://php.net/manual/en/function.session-start.php
     */
    static function clearDuplicateCookies()
    {
        // If headers have already been sent, there's nothing we can do
        if (headers_sent()) {
            return;
        }

        $cookies = array();
        foreach (headers_list() as $header) {
            // Identify cookie headers
            if (strpos($header, 'Set-Cookie:') === 0) {
                $cookies[] = $header;
            }
        }
        // Removes all cookie headers, including duplicates
        header_remove('Set-Cookie');

        // Restore one copy of each cookie
        foreach(array_unique($cookies) as $cookie) {
            header($cookie, false);
        }
    }
   
   
   static function getValue(string $key, string $session_n, $session_id)
    {
        if(strlen($session_n)>0){
           session_name($session_n);
        }
        if(isset($session_id) && !is_null($session_id)){
           session_id($session_id);
        } else {
           session_id($_COOKIE["PHPSESSID"]);
        }
        //ini_set('session.use_only_cookies', 1);
        //ini_set('session.cookie_httponly', 1);
        session_start([
            'cookie_lifetime' => __SESSION_EXPIRATION__,
            'use_only_cookies' => TRUE,
            'cookie_httponly' => TRUE
        ]);
        $answer = $_SESSION[$key];
        session_write_close();
        self::clearDuplicateCookies();
        return $answer;
    }
    
    
    static function isKey(string $key, string $session_n, $session_id)
    {
        if(strlen($session_n)>0){
           session_name($session_n);
        }
        if(isset($session_id) && !is_null($session_id)){
           session_id($session_id);
        } else {
           session_id($_COOKIE["PHPSESSID"]);
        }
        //ini_set('session.use_only_cookies', 1);
        //ini_set('session.cookie_httponly', 1);
        session_start([
            'cookie_lifetime' => __SESSION_EXPIRATION__,
            'use_only_cookies' => TRUE,
            'cookie_httponly' => TRUE
        ]);
        $answer = isset($_SESSION[$key]);
        session_write_close();
        self::clearDuplicateCookies();
        return $answer;
    }
    
    static function setValue($value, string $key, string $session_n, $session_id)
    {
        if(!is_resource($value)){
            if(strlen($session_n)>0){
                session_name($session_n);
            }
            if(isset($session_id) && !is_null($session_id)){
                session_id($session_id);
            } else {
                session_id($_COOKIE["PHPSESSID"]);
            }
            //ini_set('session.use_only_cookies', 1);
            //ini_set('session.cookie_httponly', 1);
            session_start([
                'cookie_lifetime' => __SESSION_EXPIRATION__,
                'use_only_cookies' => TRUE,
                'cookie_httponly' => TRUE
            ]);
            $_SESSION[$key] = $value;
            session_write_close();
            self::clearDuplicateCookies();
        }
    }
    
    
    static function removeKey(string $key, string $session_n, $session_id)
    {
        if(strlen($session_n)>0){
            session_name($session_n);
        }
        if(isset($session_id) && !is_null($session_id)){
            session_id($session_id);
        } else {
            session_id($_COOKIE["PHPSESSID"]);
        }
        //ini_set('session.use_only_cookies', 1);
        //ini_set('session.cookie_httponly', 1);
        session_start([
            'cookie_lifetime' => __SESSION_EXPIRATION__,
            'use_only_cookies' => TRUE,
            'cookie_httponly' => TRUE
        ]);
        unset($_SESSION[$key]);
        session_write_close();
        self::clearDuplicateCookies();
    }

    static protected function isValidSession() : bool
   {
            if(!isset($_SESSION['IP_ADDRESS']) || !isset($_SESSION['USER_AGENT']))
                return false;

            if($_SESSION['IP_ADDRESS']!=$_SERVER['REMOTE_ADDR'])
                return false;

            if($_SESSION['USER_AGENT']!=$_SERVER['HTTP_USER_AGENT'])
                return false;

            return true;
    }
    
    static function regenerateSession()
    {
        foreach ($_SESSION as $key => $value) {
            error_log(__METHOD__ . " at the beginning the contents of SESSION: {$key} => {$value}");
        }
        foreach ($_COOKIE as $key => $value) {
            error_log(__METHOD__ . " at the beginning the contents of COOKIE: {$key} => {$value}");
        }
        // If this session is obsolete it means there already is a new identifier
        if(isset($_SESSION['OBSOLETE']) && $_SESSION['OBSOLETE'] == true)
            return;

        // Set current session to expire in ## seconds
        $_SESSION['OBSOLETE'] = TRUE;
        $_SESSION['EXPIRES'] = time() + 60;

        // Create new session without destroying the old one
        session_regenerate_id(FALSE);

        // Grab current session ID and close both sessions to allow other scripts to use them
        $new_session = session_id();
        session_write_close();

        // Set session ID to the new one, and start it back up again
        session_id($new_session);
        session_start();
        setcookie(session_name(), $new_session, time() + __SESSION_EXPIRATION__,
                "/test/OnSet/", substr(__DOMAIN_SANS_WWW__, 8, strpos(__DOMAIN_SANS_WWW__, "/", 8)),
                TRUE, TRUE);

        // Now we unset the obsolete and expiration values for the session we want to keep
        unset($_SESSION['OBSOLETE']);
        unset($_SESSION['EXPIRES']);
        
        foreach ($_SESSION as $key => $value) {
            error_log(__METHOD__ . " at the end the contents of SESSION: {$key} => {$value}");
        }
        foreach ($_COOKIE as $key => $value) {
            error_log(__METHOD__ . " at the end the contents of COOKIE: {$key} => {$value}");
        }
    }
    
    static protected function isFreshSession() : bool
    {
        if(isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES']))
            return false;

        if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time())
            return false;

        return true;
    }
}