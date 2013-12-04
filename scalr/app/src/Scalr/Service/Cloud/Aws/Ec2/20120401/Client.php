<?php
    abstract class Scalr_Service_Cloud_Aws_Ec2_20120401_Client extends Scalr_Service_Cloud_Aws_Transports_Query
    {
        function __construct()
        {
            $this->apiVersion = '2012-04-01';
            $this->uri = '/';
            $this->responseFormat = 'Object';
        }

        public function describeVolumes(array $volumes = null, array $filters = null)
        {
            $request_args = array(
                "Action" => "DescribeVolumes",
            );
            foreach ((array)$volumes as $i=>$n)
                $request_args['VolumeId.'.($i+1)] = $n;

            $i = 1;
            foreach ((array)$filters as $name=>$value) {
                $request_args['Filter.'.($i+1).'.Name'] = $name;
                $request_args['Filter.'.($i+1).'.Value.1'] = $value;
                $i++;
            }

            return $this->request("GET", $this->uri, $request_args);
        }
    }

?>