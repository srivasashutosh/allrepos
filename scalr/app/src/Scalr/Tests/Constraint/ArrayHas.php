<?php
namespace Scalr\Tests\Constraint;

/**
 * ArrayHas constraint class
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.11.2012
 */
class ArrayHas extends \PHPUnit_Framework_Constraint
{
    /**
     * Array key
     *
     * @var string
     */
    protected $arrayKey;

    /**
     * Constraint that should be applied to array value at specified index.
     *
     * @var \PHPUnit_Framework_Constraint
     */
    protected $constraint;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Constructor
     *
     * @param   \PHPUnit_Framework_Constraint $constraint A constraint which should be applied to the specified value.
     * @param   string                        $arrayKey   An index of the associated array which should be tested.
     */
    public function __construct(\PHPUnit_Framework_Constraint $constraint, $arrayKey)
    {
        $this->constraint = $constraint;
        $this->arrayKey = $arrayKey;
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_Constraint::count()
     */
    public function count()
    {
        return count($this->constraint) + 1;
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_Constraint::evaluate()
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!array_key_exists($this->arrayKey, $other)) {
            return false;
        }
        $this->value = $other[$this->arrayKey];
        return $this->constraint->evaluate($other[$this->arrayKey]);
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_Constraint::fail()
     */
    public function fail($other, $description, \PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        parent::fail($other[$this->arrayKey], $description, $comparisonFailure);
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_SelfDescribing::toString()
     */
    public function toString()
    {
        return sprintf('the value of key "%s"(%s) %s', $this->arrayKey, $this->value, $this->constraint->toString());
    }

    /**
     * {@inheritdoc}
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf('Failed asserting that %s.', $this->toString());
    }
}