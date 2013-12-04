<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * MarkerType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  string              $marker   Use this when paginating results to indicate where to begin in
 *                                          your list of distributions. The results include distributions in the
 *                                          list that occur after the marker. To get the next page of results,
 *                                          set the Marker to the value of the NextMarker from the current
 *                                          page's response (which is also the ID of the last distribution on
 *                                          that page).
 * @property  string              $maxItems The maximum number of items you want in the response body.
 *
 * @method    string              getMarker()          getMarker()         Gets a marker.
 * @method    MarkerType          setMarker()          setMarker($val)     Sets a marker.
 * @method    string              getMaxItems()        getMaxItems()       Gets maxItems.
 * @method    MarkerType          setMaxItems()        setMaxItems($val)   Sets maxItems.
 */
class MarkerType extends AbstractCloudFrontDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('marker', 'maxItems');

    /**
     * Convenient constructor
     *
     * @param   string     $marker   optional The marker
     * @param   string     $maxItems optional The maxItems
     */
    public function __construct($marker = null, $maxItems = null)
    {
        if ($marker !== null) $this->setMarker($marker);
        if ($maxItems !== null) $this->setMaxItems($maxItems);
    }
}