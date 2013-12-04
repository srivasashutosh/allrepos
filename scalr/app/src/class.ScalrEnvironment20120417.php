<?

    class ScalrEnvironment20120417 extends ScalrEnvironment20100923
    {
            /**
         * @return DOMDocument
         */
        protected function GetServerUserData()
        {
            $ResponseDOMDocument = $this->CreateResponse();

            $userData = $this->DBServer->GetCloudUserData();

            $ParamsDOMNode = $ResponseDOMDocument->createElement("user-data");

            foreach ($userData as $name => $value)
            {
                $ParamDOMNode = $ResponseDOMDocument->createElement("key");
                $ParamDOMNode->setAttribute("name", $name);

                $ValueDomNode = $ResponseDOMDocument->createElement("value");
                $ValueDomNode->appendChild($ResponseDOMDocument->createCDATASection($value));

                $ParamDOMNode->appendChild($ValueDomNode);
                $ParamsDOMNode->appendChild($ParamDOMNode);
            }

            $ResponseDOMDocument->documentElement->appendChild($ParamsDOMNode);

            return $ResponseDOMDocument;
        }
    }
?>