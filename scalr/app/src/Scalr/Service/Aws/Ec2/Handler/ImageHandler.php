<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\RegisterImageData;
use Scalr\Service\Aws\Ec2\DataType\CreateImageRequestData;
use Scalr\Service\Aws\Ec2\DataType\ImageFilterList;
use Scalr\Service\Aws\Ec2\DataType\ImageData;
use Scalr\Service\Aws\Ec2\DataType\ImageList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * ImageHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.01.2013
 */
class ImageHandler extends AbstractEc2Handler
{

    /**
     * Gets VolumeData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string   $imageId  Unique Identifier.
     * @return  \Scalr\Service\Aws\Ec2\DataType\ImageData|null    Returns ImageData if it does exist in the cache or NULL otherwise.
     */
    public function get($imageId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Image')->find($imageId);
    }

    /**
     * DescribeImages action
     *
     * Describes the images (AMIs, AKIs, and ARIs) available to you. Images available to you include public
     * images, private images that you own, and private images owned by other AWS accounts but for which
     * you have explicit launch permissions.
     *
     * @param   ListDataType|array|string    $imageIdList      optional One or more AMI IDs.
     * @param   ListDataType|array|string    $ownerList        optional The AMIs owned by the specified owner. Multiple owner
     *                                                         values can be specified. The IDs amazon, aws-marketplace,
     *                                                         and self can be used to include AMIs owned by Amazon, AWS Marketplace,
     *                                                         or AMIs owned by you, respectively.
     *                                                         Valid values: amazon | aws-marketplace | self | AWS account ID | all
     * @param   ImageFilterList|array        $filter           optional Filter list
     * @param   ListDataType|array|string    $executableByList optional The AMIs for which the specified user ID has explicit
     *                                                         launch permissions. The user ID can be an AWS account
     *                                                         ID, self to return AMIs for which the sender of the request
     *                                                         has explicit launch permissions, or all to return AMIs with
     *                                                         public launch permissions.
     * @return  ImageList Returns the list of Images on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($imageIdList = null, $ownerList = null, $filter = null, $executableByList = null)
    {
        if ($imageIdList !== null && !($imageIdList instanceof ListDataType)) {
            $imageIdList = new ListDataType($imageIdList);
        }
        if ($ownerList !== null && !($ownerList instanceof ListDataType)) {
            $ownerList = new ListDataType($ownerList);
        }
        if ($executableByList !== null && !($executableByList instanceof ListDataType)) {
            $executableByList = new ListDataType($executableByList);
        }
        if ($filter !== null && !($filter instanceof ImageFilterList)) {
            $filter = new ImageFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeImages($imageIdList, $ownerList, $filter, $executableByList);
    }

    /**
     * CreateImage action
     *
     * Creates an Amazon EBS-backed AMI from an Amazon EBS-backed instance that is either running or
     * stopped.
     * Note! If you customized your instance with instance store volumes or EBS volumes in addition to the
     * root device volume, the new AMI contains block device mapping information for those volumes.
     * When you launch an instance from this new AMI, the instance automatically launches with those
     * additional volumes.
     *
     * @param   CreateImageRequestData     $request   Request object
     * @return  string Returns ID of the created image on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create(CreateImageRequestData $request)
    {
        return $this->getEc2()->getApiHandler()->createImage($request);
    }

    /**
     * RegisterImage action
     *
     * Registers a new AMI with Amazon EC2. When you're creating an AMI, this is the final step you must
     * complete before you can launch an instance from the AMI
     *
     * @param   RegisterImageData $request Register Image request
     * @return  string            Returns the ID of the newly registered AMI
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function register(RegisterImageData $request)
    {
        return $this->getEc2()->getApiHandler()->registerImage($request);
    }

    /**
     * DeregisterImage action
     *
     * Deregisters the specified AMI. Once deregistered, the AMI cannot be used to launch new instances.
     * Note! This command does not delete the AMI.
     *
     * @param   string      $imageId The ID of the AMI
     * @return  bool        Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deregister($imageId)
    {
        return $this->getEc2()->getApiHandler()->deregisterImage($imageId);
    }

    /**
     * CopyImage action
     *
     * Initiates the copy of an AMI from the specified source region to the region in which the API call is executed
     *
     * @param   string     $srcRegion     The ID of the AWS region that contains the AMI to be copied.
     * @param   string     $srcImageId    The ID of the Amazon EC2 AMI to copy.
     * @param   string     $name          optional The name of the new Amazon EC2 AMI.
     * @param   string     $description   optional A description of the new EC2 AMI in the destination region.
     * @param   string     $clientToken   optional Unique, case-sensitive identifier you provide to ensure
     *                                             idempotency of the request
     * @param   string     $destRegion    optional The ID of the destination region.
     * @return  string     Returns ID of the created image on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function copy($srcRegion, $srcImageId, $name = null, $description = null,
                        $clientToken = null, $destRegion = null)
    {
        return $this->getEc2()->getApiHandler()->copyImage(
            $srcRegion, $srcImageId, $name, $description, $clientToken, $destRegion
        );
    }
}