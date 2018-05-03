<?php
namespace Segment\Model;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");
/*require_once ('/home/luvmachi/public_html/christinamarvel.com/test/utilities/variables.php');
require_once(__ROOT__ . '/utilities/templates.php');*/

/**
 * Singleton Database Access Object
 */
interface QueryHandler
{
    public static function getQueryHandler();
    public static function describeTable($table_name_string);
    public function query(AbstractStatement $query_statement_string, array $where_values, QHFetchArgBuilder $fetch);
}