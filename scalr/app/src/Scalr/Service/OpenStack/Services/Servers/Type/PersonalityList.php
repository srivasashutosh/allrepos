<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\AbstractList;

/**
 * PersonalityList
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class PersonalityList extends AbstractList
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.AbstractList::getClass()
     */
    public function getClass()
    {
        return __NAMESPACE__ . '\\Personality';
    }
}