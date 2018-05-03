<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface SelectClauseBuilder extends ClauseBuilder
{

    /**
     * Add a column to be returned by query
     * @param Column $clmn
     */
    public function addReturnColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the mean value of which to be returned.
     * @param Column $clmn
     */
    public function addReturnMeanColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the unique values found of which to be returned
     * @param Column $clmn
     */
    public function addReturnUniqueColumn(Column $clmn);

    /**
     * Add a column, the modal average of which to be returned
     * @param Column $clmn
     */
    public function addReturnModeColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the median average of which to be returned
     * @param Column $clmn
     */
    public function addReturnMedianColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the maximum value of which to be returned
     * @param Column $clmn
     */
    public function addReturnMaxColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the minimum value of which to be returned
     * @param Column $clmn
     */
    public function addReturnMinColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add a column, the values of which to be sorted
     * @param Column $clmn
     * @param boolean $asc Ascending: true. Descending: false.
     */
    public function addReturnSortColumn(Column $clmn, $asc = TRUE);

    /**
     * Add limitation to number of records returned
     * @param boolean $absolute_amnt Regarding $amnt: Scalar: TRUE; Percentage: FALSE
     * @param integer $amnt The number, or percentage, of records returned.
     * @param boolean $absolute_start Optional. Default TRUE. Regarding $start: Scalar: TRUE; Percentage: FALSE
     * @param integer $start Optional. Default 0 (zero). The starting index or percentage
     */
    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0);

    public function addReturnCountColumn(Column $clmn, production\StringValue $alias = NULL);

    /**
     * Add expression to be executed.
     * @param Expression $exp
     * @param boolean $parenthesis TRUE expression to be wrapped in parenthesis.
     * @param production\StringValue $alias
     */
    public function addReturnExpression(Expression $exp, $parenthesis = FALSE, production\StringValue $alias = NULL);

    /**
     * All of the records from each query together.
     * @param Statement $first_var First query of the union
     * @param Statement $var_comp One or more additional queries of the union
     */
    public function addUnion(Statement $first_var, Statement ...$var_comp);

    /**
     * An intersection of the results of each subquery.
     * @param Statement $first_var First query of the intersection
     * @param Statement $var_comp One or more additional queries of the intersection
     */
    public function addIntersection(Statement $first_var, Statement ...$var_comp);
    
    
}