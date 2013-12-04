<?php
class Scalr_UI_Controller_Dashboard extends Scalr_UI_Controller
{
    public function hasAccess()
    {
        return true;
    }

    public function defaultAction()
    {
        if ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN) {
            $this->response->page('ui/dashboard/admin.js');
        } else {
            $loadJs = array('ui/dashboard/columns.js');
            $cloudynEnabled = \Scalr::config('scalr.cloudyn.master_email') ? true : false;
            $billingEnabled = \Scalr::config('scalr.billing.enabled') ? true : false;

            $panel = $this->user->getDashboard($this->getEnvironmentId());

            if (empty($panel['configuration'])) {
                // default configurations
                $client = Client::Load($this->user->getAccountId());
                if ($client->GetSettingValue(CLIENT_SETTINGS::DATE_FARM_CREATED)) {
                    // old customer
                    $panel['configuration'] = array(
                        array(
                            array('name' => 'dashboard.status')
                        ),
                        array(
                            array('name' => 'dashboard.announcement', 'params' => array('newsCount' => 5)),
                            array('name' => 'dashboard.usagelaststat', 'params' => array('farmCount' => 5))
                        ),
                        array(
                            array('name' => 'dashboard.lasterrors', 'params' => array('errorCount' => 10)),
                            array('name' => 'dashboard.uservoice', 'params' => array('sugCount' => 5))
                        )
                    );

                    if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER && $billingEnabled)
                        array_unshift($panel['configuration'][0], array('name' => 'dashboard.billing'));

                } else {
                    // new customer
                    $panel['configuration'] = array(
                        array(
                            array('name' => 'dashboard.tutorapp')
                        ),
                        array(
                            array('name' => 'dashboard.tutordns')
                        ),
                        array(
                            array('name' => 'dashboard.tutorfarm'),
                            array('name' => 'dashboard.announcement', 'params' => array('newsCount' => 5))
                        )
                    );

                    if ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER && $billingEnabled)
                        $panel['configuration'][1][] = array('name' => 'dashboard.billing');
                }

                $this->user->setDashboard($this->getEnvironmentId(), $panel);
                $panel = $this->user->getDashboard($this->getEnvironmentId());
            }

            // section for adding required widgets
            if ($cloudynEnabled &&
                !in_array('cloudynInstalled', $panel['flags']) &&
                !in_array('dashboard.cloudyn', $panel['widgets']) &&
                !!$this->environment->isPlatformEnabled(SERVER_PLATFORMS::EC2))
            {
                if (! isset($panel['configuration'][0])) {
                    $panel['configuration'][0] = array();
                }
                array_unshift($panel['configuration'][0], array('name' => 'dashboard.cloudyn'));
                $panel['flags'][] = 'cloudynInstalled';
                $this->user->setDashboard($this->getEnvironmentId(), $panel);
                $panel = $this->user->getDashboard($this->getEnvironmentId());
            }

            $panel = $this->fillDash($panel);

            $this->response->page('ui/dashboard/view.js',
                array(
                    'panel' => $panel,
                    'flags' => array(
                        'cloudynEnabled' => $cloudynEnabled,
                        'billingEnabled' => $billingEnabled
                    )
                ),
                $loadJs,
                array('ui/dashboard/view.css')
            );
        }
    }

    public function fillDash($panel)
    {
        foreach ($panel['configuration'] as &$column) {
            foreach ($column as &$wid) {
                $tt = microtime(true);

                $name = str_replace('dashboard.', '', $wid['name']);
                try {
                    $widget = Scalr_UI_Controller::loadController($name, 'Scalr_UI_Controller_Dashboard_Widget');
                } catch (Exception $e) {
                    continue;
                }

                $info = $widget->getDefinition();

                if (!empty($info['js']))
                    $loadJs[] = $info['js'];

                if ($info['type'] == 'local') {
                    $wid['widgetContent'] = $widget->getContent($wid['params']);
                    $wid['time'] = microtime(true) - $tt;
                }
            }
        }
        return $panel;
    }

    public function xSavePanelAction()
    {
        $t = microtime(true);
        $this->request->defineParams(array(
           'panel' => array('type' => 'json')
        ));

        $this->user->setDashboard($this->getEnvironmentId(), $this->getParam('panel'));
        $panel = $this->user->getDashboard($this->getEnvironmentId());

        $t2 = microtime(true);
        $panel = $this->fillDash($panel);
        $t3 = microtime(true);

        $this->response->data(array(
            'panel' => $panel,
            't' => microtime(true) - $t,
            't2' => microtime(true) - $t2,
            't3' => microtime(true) - $t3,
        ));
    }

    public function xUpdatePanelAction()
    {
        $this->request->defineParams(array(
            'widget' => array('type' => 'json')
        ));

        $panel = $this->user->getDashboard($this->getEnvironmentId());
        if (!strpos(json_encode($panel['configuration']), json_encode($this->getParam('widget')))) {
            $this->user->addDashboardWidget($this->getEnvironmentId(), $this->getParam('widget'));
        }

        $panel = $this->user->getDashboard($this->getEnvironmentId());
        $panel = $this->fillDash($panel);

        $this->response->success('New widget successfully added to dashboard');
        $this->response->data(array('panel' => $panel));
    }


    public function checkLifeCycle($widgets)
    {
        $result = array();

        foreach ($widgets as $id => $object) {
            $name = str_replace('dashboard.', '', $object['name']);

            try {
                $widget = Scalr_UI_Controller::loadController($name, 'Scalr_UI_Controller_Dashboard_Widget');
            } catch (Exception $e) {
                continue;
            }

            $result[$id] = $widget->getContent($object['params']);
        }

        return $result;
    }

    public function xAutoUpdateDashAction () {
        $this->request->defineParams(array(
            'updateDashboard' => array('type' => 'json')
        ));
        $response = array(
            'updateDashboard' => ''
        );
        $widgets = $this->getParam('updateDashboard');
        if ($this->user) {
            if ($widgets && !empty($widgets)) {
                $response['updateDashboard'] = $this->checkLifeCycle($widgets);
            }
        }
        $this->response->data($response);
    }
}
