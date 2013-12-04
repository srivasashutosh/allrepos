<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\Marker;
use \DateTime;

/**
 * ListImagesFilter
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.12.2012
 */
class ListImagesFilter extends Marker
{

    /**
     * Server reference
     * @var string
     */
    private $serverId;

    /**
     * Filters the list of images by name.
     * @var string
     */
    private $name;

    /**
     * Filters the list of images by status
     * @var ImageStatus
     */
    private $status;

    /**
     * Filters the list of images to those that have changed since the changes-since time
     * @var DateTime
     */
    private $changesSince;

    /**
     * Filters base OpenStack images or any custom server images that you have create
     * @var ImageType
     */
    private $type;

    /**
     * Convenient constructor
     *
     * @param   string      $name         optional An image name.
     * @param   string      $serverId     optional An server reference.
     * @param   ImageStatus $status       optional An Image status.
     * @param   ImageType   $type         optional An image type.
     * @param   \DateTime   $changesSince optional A changes-since time.
     * @param   string      $marker       optional A marker.
     * @param   int         $limit        optional Limit.
     */
    public function __construct($name = null, $serverId = null, ImageStatus $status = null,
                                ImageType $type, \DateTime $changesSince, $marker = null, $limit = null)
    {
        parent::__construct($marker, $limit);
    }

    /**
     * Convenient constructor
     *
     * @param   string      $name         optional An image name.
     * @param   string      $serverId     optional An server reference.
     * @param   ImageStatus $status       optional An Image status.
     * @param   ImageType   $type         optional An image type.
     * @param   \DateTime   $changesSince optional A changes-since time.
     * @param   string      $marker       optional A marker.
     * @param   int         $limit        optional Limit.
     * @return  ListImagesFilter Returns new ListImagesFilter object.
     */
    public static function init($name = null, $serverId = null, ImageStatus $status = null,
                                ImageType $type = null, \DateTime $changesSince = null, $marker = null, $limit = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets server Id
     *
     * @return  string  Server ID.
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Gets an image name.
     *
     * @return  string An image name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets an Image Status
     *
     * @return  ImageStatus An image Status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets Changes-since time.
     *
     * @return  DateTime The changes-since time
     */
    public function getChangesSince()
    {
        return $this->changesSince;
    }

    /**
     * Gets Image type
     *
     * @return  ImageType The Image type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the server ID
     *
     * @param   string   $serverId  The server ID.
     * @return  ListImagesFilter
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;
        return $this;
    }

    /**
     * Sets a image name.
     *
     * @param   string $name An Image name.
     * @return  ListImagesFilter
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets an Image status.
     *
     * @param   ImageStatus $status An image status.
     * @return  ListImagesFilter
     */
    public function setStatus(ImageStatus $status = null)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets changes-since time
     *
     * @param   DateTime $changesSince The changes-since time.
     * @return  ListImagesFilter
     */
    public function setChangesSince(DateTime $changesSince = null)
    {
        $this->changesSince = $changesSince;
        return $this;
    }

    /**
     * Sets an image type.
     *
     * @param   ImageType $type An image type
     * @return  ListImagesFilter
     */
    public function setType(ImageType $type = null)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.Marker::getQueryData()
     */
    public function getQueryData()
    {
        $options = parent::getQueryData();
        if ($this->getChangesSince() !== null) {
            $options['changes-since'] = $this->getChangesSince()->format('c');
        }
        if ($this->getServerId() !== null) {
            $options['server'] = (string) $this->getServerId();
        }
        if ($this->getName() !== null) {
            $options['name'] = (string) $this->getName();
        }
        if ($this->getStatus() !== null) {
            $options['status'] = (string) $this->getStatus();
        }
        if ($this->getType() !== null) {
            $options['type'] = (string) $this->getType();
        }
        return $options;
    }
}