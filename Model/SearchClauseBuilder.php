<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface SearchClauseBuilder extends ClauseBuilder
{
    /**
     * Query will require column of records to equal a supplied value. Optionally, the
     * value may be derived dynamically from another query.
     * @param Column $clmn
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqual(Column $clmn, production\SingleValue $value = NULL, Statement $var_comp = NULL,
            $required_param = TRUE);

    /**
     * Query will require all records to equal any supplied value for the respective column.
     * Values for comparison may come in part or whole another query.
     * @param Column $clmn
     * @param Statement $var_comp
     * @param AnyAllValues $values
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqualAny(Column $clmn, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all records to be within a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive Exclusive: true. Inclusive: false.
     * @param BetweenValues $values
     * @param Statement $var_comp1 Least value
     * @param Statement $var_comp2 Most value
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchRange(Column $clmn, $exclusive = TRUE, production\BetweenValues $values = NULL,
            Statement $var_comp1 = NULL, Statement $var_comp2 = NULL, $required_param = TRUE);

    /**
     * Query will require all records to be outside a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive
     * @param BetweenValues $values
     * @param Statement $var_comp1
     * @param Statement $var_comp2
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchNotRange(Column $clmn, $exclusive = TRUE, production\BetweenValues $values = NULL,
            Statement $var_comp1 = NULL, Statement $var_comp2 = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values greater than the comparison
     * for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreater(Column $clmn, $exclusive = TRUE, production\SingleValue $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values greater than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAny(Column $clmn, $exclusive = TRUE, production\AnyAllValues $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values greater than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAll(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values less than the comparison
     * value for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesser(Column $clmn, $exclusive = TRUE, production\SingleValue $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values less than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAny(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    /**
     * Query will require all returned records to have values less than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAll(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);

    public function addSearchWildcard(Column $clmn, production\SingleValue $value, $required_param = TRUE);

}