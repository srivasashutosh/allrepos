<?
    final class SERVER_PLATFORMS
    {
        const EC2		= 'ec2';
        const RACKSPACE = 'rackspace';
        const EUCALYPTUS= 'eucalyptus';
        const NIMBULA	= 'nimbula';
        const GCE		= 'gce';

        // Openstack based
        const OPENSTACK = 'openstack';

        const RACKSPACENG_US = 'rackspacengus';
        const RACKSPACENG_UK = 'rackspacenguk';


        // Cloudstack based
        const CLOUDSTACK = 'cloudstack';
        const IDCF		= 'idcf';
        const UCLOUD	= 'ucloud';


        public static function GetList()
        {
            return array(
                self::GCE			=> 'Google CE',
                self::EC2 			=> 'Amazon EC2',
                self::EUCALYPTUS 	=> 'Eucalyptus',
                self::RACKSPACE		=> 'Rackspace',
                self::NIMBULA		=> 'Nimbula',
                self::CLOUDSTACK	=> 'Cloudstack',
                self::OPENSTACK		=> 'Openstack',
                self::IDCF			=> 'IDC Frontier',
                self::UCLOUD		=> 'KT uCloud',
                self::RACKSPACENG_US=> 'Rackspace Open Cloud (US)',
                self::RACKSPACENG_UK=> 'Rackspace Open Cloud (UK)'
            );
        }

        public static function GetName($const)
        {
            $list = self::GetList();

            return $list[$const];
        }
    }
?>