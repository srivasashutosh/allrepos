<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\Marker;
use \DateTime;

/**
 * ListServersFilter
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    10.12.2012
 */
class ListServersFilter extends Marker
{
    /**
     * The image ID
     * @var string
     */
    private $imageId;

    /**
     * The flavor ID.
     * @var string
     */
    private $flavorId;

    /**
     * The server name
     *
     * @var string
     */
    private $name;

    /**
     * The server status. Servers contain a status attribute that
     * indicates the current server state. You can filter on the
     * server status when you complete a list servers request,
     * and the server status is returned in the response body
     *
     * @var ServerStatus
     */
    private $status;

    /**
     * The changes-since time. The list contains servers that have been deleted since the changes-since time.
     *
     * @var DateTime
     */
    private $changesSince;

    /**
     * Convenient constructor
     *
     * @param   string       $name         optional A Server name.
     * @param   string       $flavorId     optional A Flavor ID.
     * @param   string       $imageId      optional An Image ID.
     * @param   ServerStatus $status       optional A server status.
     * @param   DateTime     $changesSince optional A changes-since date.
     * @param   string       $marker       optional Marker.
     * @param   number       $limit        optional Limit.
     */
    public function __construct($name = null, $flavorId = null, $imageId = null, ServerStatus $status = null,
                                DateTime $changesSince = null, $marker = null, $limit = null)
    {
        parent::__construct($marker, $limit);
        $this
            ->setName($name)
            ->setFlavorId($flavorId)
            ->setImage($imageId)
            ->setStatus($status)
            ->setChangesSince($changesSince)
        ;
    }

    /**
     * Creates a new object
     *
     * @param   string   $name         optional A Server name.
     * @param   string   $flavorId     optional A Flavor ID.
     * @param   string   $imageId      optional An Image ID.
     * @param   string   $status       optional A server status.
     * @param   DateTime $changesSince optional A changes-since date.
     * @param   string   $marker       optional Marker.
     * @param   number   $limit        optional Limit.
     * @return  ListServersFilter      Creates new object and returns it.
     */
    public static function init($name = null, $flavorId = null, $imageId = null, $status = null,
                                DateTime $changesSince = null, $marker = null, $limit = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets the image ID
     *
     * @return  string The image ID
     */
    public function getImage()
    {
        return $this->imageId;
    }

    /**
     * Gets a Flavor ID
     *
     * @return  string The flavor ID
     */
    public function getFlavorId()
    {
        return $this->flavorId;
    }

    /**
     * Gets a server name.
     *
     * @return  string The server name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets a status
     *
     * @return  ServerStatus The server status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the changes-since date.
     *
     * @return  DateTime The changes-since date.
     */
    public function getChangesSince()
    {
        return $this->changesSince;
    }

    /**
     * Sets an Image ID
     *
     * @param   string $imageId An Image ID.
     * @return  ListServersType
     */
    public function setImage($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * Sets a Flavor ID
     *
     * @param   string $flavorId A FlavorID
     * @return  ListServersType
     */
    public function setFlavorId($flavorId)
    {
        $this->flavorId = $flavorId;
        return $this;
    }

    /**
     * Sets a server Name
     *
     * @param   string $name A Server Name.
     * @return  ListServersType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets a server status
     *
     * @param   ServerStatus $status  A Server Status.
     * @return  ListServersType
     */
    public function setStatus(ServerStatus $status = null)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets a changes-since date.
     *
     * @param   DateTime $changesSince The changes-since date.
     * @return  ListServersType
     */
    public function setChangesSince(DateTime $changesSince = null)
    {
        $this->changesSince = $changesSince;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.Marker::setLimit()
     * @return ListServersType
     */
    public function setLimit($limit)
    {
        return parent::setLimit($limit);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.Marker::setMarker()
     * @return ListServersType
     */
    public function setMarker($marker)
    {
        return parent::setMarker($marker);
    }

    /**
     * Gets query data array
     *
     * @return array Returns query data array
     */
    public function getQueryData()
    {
        $options = parent::getQueryData();
        if ($this->getChangesSince() !== null) {
            $options['changes-since'] = $this->getChangesSince()->format('c');
        }
        if ($this->getFlavorId() !== null) {
            $options['flavor'] = (string) $this->getFlavorId();
        }
        if ($this->getImage() !== null) {
            $options['image'] = (string) $this->getImage();
        }
        if ($this->getName() !== null) {
            $options['name'] = (string) $this->getName();
        }
        if ($this->getStatus() !== null) {
            $options['status'] = (string) $this->getStatus();
        }
        return $options;
    }

}