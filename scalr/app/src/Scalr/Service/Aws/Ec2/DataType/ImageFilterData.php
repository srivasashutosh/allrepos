<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ImageFilterData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    23.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ImageFilterNameType    $name                       A filter key name
 * @property array                                                  $value                      An array of values
 *
 * @method   \Scalr\Service\Aws\Ec2\DataType\ImageFilterNameType    getName()     getName()     Gets filter key name.
 * @method   array                                                  getValue()    getValue()    Gets list of values.
 */
class ImageFilterData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('name', 'value');

    /**
     * Convenient constuctor for the filter
     *
     * @param ImageFilterNameType $name  Filter name
     * @param array|string        $value Filter value
     */
    public function __construct(ImageFilterNameType $name = null, $value = null)
    {
        parent::__construct();
        $this->setValue($value);
        $this->setName($name);
    }

    /**
     * Sets a filter key name.
     *
     * @param   ImageFilterNameType $name Filter key name
     * @return  ImageFilterData
     */
    public function setName(ImageFilterNameType $name = null)
    {
        return $this->__call(__FUNCTION__, array($name));
    }

    /**
     * Sets a filter values.
     *
     * @param   string|array $value Value of list of the values for the filter
     * @return  ImageFilterData
     */
    public function setValue($value = null)
    {
        if ($value !== null && !is_array($value)) {
            $value = array((string)$value);
        }
        return $this->__call(__FUNCTION__, array($value));
    }
}