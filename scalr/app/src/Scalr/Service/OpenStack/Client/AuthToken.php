<?php
namespace Scalr\Service\OpenStack\Client;

use Scalr\Service\OpenStack\Exception\OpenStackException;

/**
 * AuthToken
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class AuthToken implements \Serializable
{
    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var string
     */
    private $tenantName;

    /**
     * Auth document
     * @var \stdClass
     */
    private $authDocument;

    /**
     * Region endpoints
     * @var array
     */
    private $regionEndpoints;

    /**
     * Regions
     * @var array
     */
    private $zones;

    /**
     * Gets an expiration date
     *
     * @return  \DateTime Returns Expiration date of the token
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Gets an token
     *
     * @return  string Returns token
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets a tenant id.
     *
     * @return  string Returns a tenant id.
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * Gets a tenant name.
     *
     * @return  string  Returns a tenant name.
     */
    public function getTenantName()
    {
        return $this->tenantName;
    }

    /**
     * Sets an expiration date
     *
     * @param   \DateTime   $expires
     * @return  AuthToken
     */
    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Sets a token
     *
     * @param   string   $id
     * @return  AuthToken
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Sets a tenant id
     *
     * @param   string    $tenantId
     * @return  AuthToken
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * Sets a tenant name
     *
     * @param   string    $tenantName
     * @return  AuthToken
     */
    public function setTenantName($tenantName)
    {
        $this->tenantName = $tenantName;

        return $this;
    }

    /**
     * Gets data as array
     *
     * @return  array Returns data as array
     */
    public function getData()
    {
        return array(
            ($this->expires instanceof \DateTime ? $this->expires->format('c') : null),
            $this->id, $this->tenantId, $this->tenantName, json_encode($this->authDocument),
            $this->regionEndpoints, $this->zones
        );
    }

    /**
     * {@inheritdoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize($this->getData());
    }

    /**
     * {@inheritdoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list($this->expires, $this->id, $this->tenantId,
             $this->tenantName, $this->authDocument,
             $this->regionEndpoints, $this->zones) = unserialize($serialized);
        if (!empty($this->expires)) {
            $this->expires = new \DateTime($this->expires);
        }
        if (!empty($this->authDocument)) {
            $this->authDocument = json_decode($this->authDocument);
        }
    }

    /**
     * @return string Returns json encoded representaion of the token
     */
    public function __toString()
    {
        return json_encode($this->getData());
    }

    /**
     * Creates new instance of the AuthToken using this JSON
     *
     * @param   string $jsonString JSON document that is received from authenticate responce
     * @return  AuthToken
     */
    public static function loadJson($jsonString)
    {
        $d = json_decode($jsonString);

        if (!isset($d->access->token)) {
            $invalid = true;
        }
        if (isset($invalid) || !isset($d->access->token->expires) || !isset($d->access->token->id)) {
            throw new \InvalidArgumentException("Malformed JSON document " . (string) $jsonString);
        }

        $services = array();
        $regions = array();
        if (!empty($d->access->serviceCatalog)) {
            foreach ($d->access->serviceCatalog as $srv) {
                foreach ($srv->endpoints as $srvEndpoint) {
                    $srvVersion = isset($srvEndpoint->versionId) ? $srvEndpoint->versionId . '' : '';
                    if (isset($srvEndpoint->region)) {
                        $regions[$srvEndpoint->region] = true;
                        $endpointRegion = $srvEndpoint->region;
                    } else {
                        $endpointRegion = '';
                    }
                    if (!isset($services[$srv->type][$endpointRegion][$srvVersion])) {
                        $services[$srv->type][$endpointRegion][$srvVersion] = array();
                    }
                    $services[$srv->type][$endpointRegion][$srvVersion][] = $srvEndpoint;
                }
            }
        }
        $regions = array_keys($regions);

        $ret = new AuthToken();
        $ret
            ->setExpires(new \DateTime($d->access->token->expires))
            ->setId($d->access->token->id)
            ->setAuthDocument($d)
            ->setRegionEndpoints($services)
            ->setZones($regions)
        ;
        if (isset($d->access->token->tenant->id)) {
            $ret->setTenantId($d->access->token->tenant->id);
        }
        if (isset($d->access->token->tenant->name)) {
            $ret->setTenantName($d->access->token->tenant->name);
        }

        return $ret;
    }

    /**
     * Checks whether the token is expired.
     *
     * @return  bool Returns true if token is expired or false otherwise
     */
    public function isExpired()
    {
        return $this->expires == null || $this->expires->getTimestamp() - time() - 1800 < 0 ? true : false;
    }

    /**
     * Gets Auth document
     *
     * @return  \stdClass  Auth document
     */
    public function getAuthDocument()
    {
        return $this->authDocument;
    }

    /**
     * Gets list of region endpoints
     *
     * @return  array Returns the list of the region endpoints
     */
    public function getRegionEndpoints()
    {
        return $this->regionEndpoints;
    }

    /**
     * Sets Auth document
     *
     * @param   \stdClass $authDocument Sets auth document
     * @return  AuthToken
     */
    public function setAuthDocument($authDocument)
    {
        $this->authDocument = $authDocument;
        return $this;
    }

    /**
     * Sets region endpoints
     *
     * @param   array    $regionEndpoints Region endpoints
     * @return  AuthToken
     */
    public function setRegionEndpoints($regionEndpoints)
    {
        $this->regionEndpoints = $regionEndpoints;
        return $this;
    }

    /**
     * Gets endpoint url for the given service and region
     *
     * @param   string     $type    Service Type
     * @param   string     $region  Region
     * @param   string     $version Version of the API
     * @return  string     Returns url on success or false if it isn't provided
     * @throws  OpenStackException
     */
    public function getEndpointUrl($type, $region, $version)
    {
        if (isset($this->regionEndpoints[$type][$region])) {
            $data = isset($this->regionEndpoints[$type][$region][$version]) ?
                $this->regionEndpoints[$type][$region][$version] :
                (isset($this->regionEndpoints[$type][$region]['']) ? $this->regionEndpoints[$type][$region][''] : null);
        } else {
            throw new OpenStackException(sprintf(
                'Cannot obtain endpoint url. Unavailable service "%s" or region "%s"', $type, $region
            ));
        }
        if (!isset($data[0]->publicURL)) {
            throw new OpenStackException(sprintf(
                'Cannot obtain endpoint url. Unavailable service "%s" of "%s" version for the region "%s".',
                $type, $version, $region
            ));
        }
        return trim($data[0]->publicURL);
    }

    /**
     * Gets available Zones
     *
     * @return  array  List of the zones
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * Sets available Zones
     *
     * @param   array   $zones  Zones list
     * @return  AuthToken
     */
    public function setZones(array $zones = null)
    {
        $this->zones = $zones;
        return $this;
    }
}