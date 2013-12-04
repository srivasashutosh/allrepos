<?php

class Scalr_UI_Controller_Dashboard_Widget_Uservoice extends Scalr_UI_Controller_Dashboard_Widget
{
    public function getDefinition()
    {
        return array(
            'type' => 'nonlocal'
        );
    }

    public function getContent($params = array())
    {
        $uservoice = Scalr_Uservoice::getUservoice();

        if (empty($params['sugCount'])) {
            $params['sugCount'] = 5;
        }

        $uservoiceResult = $uservoice->getListSuggests();

        if ($uservoiceResult instanceof stdClass)
            $uservoiceResult = (array)$uservoiceResult;

        $retval = array();
        if ($uservoiceResult['suggestions']) {
            foreach ($uservoiceResult['suggestions'] as $sugValue) {
                if (count($retval) >= $params['sugCount'])
                    break;

                if ($sugValue->status->key != 'completed') {
                    if($sugValue instanceof stdClass)
                        $sugValue = (array)$sugValue;
                    $retval[] = $sugValue;
                }
            }
        }

        return array('sugs' => $retval);
    }
}