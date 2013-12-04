<?php

class Scalr_Billing
{
    const SETTING_CGF_CID = 'scalr.billing.cgf.customer_id';
    const SETTING_CGF_SID = 'scalr.billing.cgf.subscription_id';
    const SETTING_PACKAGE = 'scalr.billing.package';
    const SETTING_STATUS  = 'scalr.billing.status';
    const SETTING_IS_NEW_BILLING  = 'scalr.billing';


    const PACKAGE_SEED  = 'up-to-5-servers';
    const PACKAGE_ANGEL = 'up-to-10-servers';
    const PACKAGE_VC	= 'up-to-20-servers';
    const PACKAGE_IPO   = 'up-to-40-servers';
    const PACKAGE_MONOPOLY = 'up-to-80-servers';
    const PACKAGE_WD    = 'more-than-80-servers';

    const PACKAGE_DEVELOPMENT_LEGACY = 'development';
    const PACKAGE_PRODUCTION_LEGACY = 'production';
    const PACKAGE_BETA_LEGACY = 'beta-legacy';
    const PACKAGE_MC_LEGACY = 'mission-critical';

    const PAY_AS_YOU_GO		= 'pay-as-you-go';

    private $chargify;
    private $account;
    public $subscriptionId;
    public $customerId;
    public $package;

    public function __construct()
    {
        require_once(dirname(__FILE__)."/../externals/chargify-client/class.Chargify.php");

        $this->chargify = new Chargify(
            \Scalr::config('scalr.billing.chargify_api_key'),
            \Scalr::config('scalr.billing.chargify_domain')
        );
        $this->db = \Scalr::getDb();
    }

    /**
     *
     * @return Scalr_Billing
     */
    public function loadByAccount(Scalr_Account $account)
    {
        $this->account = $account;
        $this->subscriptionId = $this->account->getSetting(self::SETTING_CGF_SID);
        $this->package = $this->account->getSetting(self::SETTING_PACKAGE);
        $this->customerId = $this->account->getSetting(self::SETTING_CGF_CID);

        return $this;
    }

    public function getAvailablePackages()
    {
        $list = array(
            self::PACKAGE_SEED => 1,
            self::PACKAGE_ANGEL => 1,
            self::PACKAGE_VC => 1,
            self::PACKAGE_IPO => 1,
            self::PACKAGE_MONOPOLY => 1
        );

        $usage = $this->account->getLimits();

        foreach ($list as $package => $f) {
            $limits = $this->getLimits($package);
            foreach ($limits as $limitName => $limitValue)
            {
                if ($limitName == Scalr_Limits::ACCOUNT_ENVIRONMENTS)
                    continue;

                $cLimit = $usage[$limitName]['usage'];
                if ($limitValue > -1) {
                    if ($cLimit > $limitValue)
                        $list[$package] = 0;
                }
            }
        }

        return $list;
    }

    public function reportScuUsage($scu, $memo)
    {
        $this->chargify->createComponentUsage($this->subscriptionId, 13490, $scu, $memo);
        return true;
    }

    public function cancelSubscription()
    {
        $this->chargify->cancelSubscription($this->subscriptionId);

        return true;
    }

    public function changePackage($newPackage)
    {
        $info = $this->getInfo();
        $includeTrial = false;
        if ($info['realState'] == 'Trialing') {
            $includeTrial = true;
        }

        $this->chargify->upgradeSubscription($this->subscriptionId, $newPackage, $includeTrial);

        $this->setPackage($newPackage);

        return true;
    }

    public function setPackage($package)
    {
        $this->account->setSetting(self::SETTING_PACKAGE, $package);
        $this->account->setSetting(Scalr_Account::SETTING_BILLING_ALERT_PAYPAL, '0');
        $this->account->setSetting(Scalr_Account::SETTING_BILLING_ALERT_OLD_PKG, '0');

        $this->setLimits($package);
    }

    private function setLimits($package)
    {
        //Reset limits
        $this->account->resetLimits();

        // Set New limits
        $this->account->setLimits($this->getLimits($package));

        return true;
    }

    public function reactivateSubscription()
    {
        $retval = $this->chargify->reactivateSubscription($this->subscriptionId);

        var_dump($retval);

        $this->account->status = Scalr_Account::STATUS_ACTIVE;
        $this->account->save();

        return true;
    }

    public function createSubscription($package, $ccNumber, $ccExpMonth, $ccExpYear, $ccCvv, $fName = "", $lName = "", $postalCode = "")
    {
        if (!$this->subscriptionId) {
            if (!$this->customerId) {

                if ($fName == "" && $lName == "") {
                    $c = explode(" ", $this->account->getOwner()->fullname);
                    $fName = array_shift($c);
                    $lName = implode(" ", $c);
                }

                $clientInfo = array(
                    'email' => $this->account->getOwner()->getEmail(),
                    'org'	=> $this->account->name,
                    'first_name' => $fName,
                    'last_name'  => $lName
                );
            } else
                $clientInfo = false;

            $subscription = $this->chargify->createSubscription($this->account->id, $package, $ccNumber, $ccExpMonth, $ccExpYear, $ccCvv, $clientInfo, $postalCode);

            $this->account->setSetting(self::SETTING_CGF_SID, $subscription['subscription']['id']);
            $this->account->setSetting(self::SETTING_CGF_CID, $subscription['subscription']['customer']['id']);
            $this->account->setSetting(self::SETTING_PACKAGE, $package);

            $this->account->status = Scalr_Account::STATUS_ACTIVE;
            $this->account->save();

            $this->setLimits($package);

            return true;
        } else
            throw new Exception("Account already have subscription");
    }

    public function updateCreditCard($ccNumber, $ccCvv, $ccExpMonth, $ccExpYear, $fName, $lName, $postalCode)
    {
        return $this->chargify->updateSubscription($this->subscriptionId, $ccNumber, $ccCvv, $ccExpMonth, $ccExpYear, $fName, $lName, $postalCode);
    }

    public function applyCoupon($code)
    {
        $customCoupon = $this->db->GetOne("SELECT chargify_coupon_id FROM billing.coupons WHERE id = ? AND scalr_account_id IS NULL", array($code));
        if ($customCoupon) {
            $origCode = $code;
            $code = $customCoupon;
        }

        $retval = $this->chargify->applyCoupon($this->subscriptionId, $code);
        $this->db->Execute("UPDATE billing.coupons SET scalr_account_id = ? WHERE id = ?", array($this->account->id, $origCode));

        return $retval;
    }

    public function setComponentValue($componentId, $value)
    {
        $this->chargify->setComponentValue($this->subscriptionId, $componentId, $value);
    }

    public function getInvoices($addText = false)
    {
        $statements = $this->chargify->getStatements($this->subscriptionId);
        $retval = array();
        foreach ($statements as $statement)
        {
            $item = array(
                'createdAt' => date("d M Y", strtotime($statement['statement']['created_at'])),
                'id' => $statement['statement']['id']
            );

            if ($addText)
                $item['text'] = $statement['statement']['basic_html_view'];

            $retval[] = $item;
        }

        return array_reverse($retval);
    }

    protected function getCurrentScuUsage()
    {
        $servers = $this->db->GetAll("SELECT server_id FROM servers WHERE client_id=? AND status =?", array($this->account->id, SERVER_STATUS::RUNNING));
        $retval = 0;
        foreach ($servers as $s) {
            $dbServer = DBServer::LoadByID($s['server_id']);
            $serverType = $dbServer->GetFlavor();
            $retval += self::getSCUByInstanceType($serverType);
        }

        return $retval;
    }

    public static function getSCUByInstanceType($serverType)
    {
        $scu = array(
            // EC2
            'm1.small'	=> 0.75,
            'm1.medium'	=> 1.5,
            'm1.large'	=> 3.25,
            'm1.xlarge' => 6.5,
            'm3.xlarge' => 6.5,
            'm3.2xlarge' => 13.75,
            't1.micro'	=> 0.25,
            'm2.xlarge'	=> 5.5,
            'm2.2xlarge'=> 11,
            'm2.4xlarge'=> 20,
            'c1.medium' => 1.75,
            'c1.xlarge'	=> 7,
            'cc1.4xlarge' => 16,
            'cc2.8xlarge' => 30,
            'cr1.8xlarge' => 45,
            'cg1.4xlarge' => 26,
            'hi1.4xlarge' => 38,
            'hs1.8xlarge' => 60,

            // Rackspace
            '1'	=> 0.25,
            '2'	=> 0.75,
            '3'	=> 1.5,
            '4'	=> 3,
            '5'	=> 6,
            '6'	=> 12,
            '7'	=> 15,

            // GCE
            'n1-standard-1-d' => 1.75,
            'n1-standard-2-d' => 3.5,
            'n1-standard-4-d' => 7,
            'n1-standard-8-d' => 14,
            'n1-standard-1'	=> 1.5,
            'n1-standard-2'	=> 3,
            'n1-standard-4'	=> 6,
            'n1-standard-8'	=> 12,

            'n1-highmem-2-d' => 4,
            'n1-highmem-4-d' => 8,
            'n1-highmem-8-d' => 16,
            'n1-highmem-2' => 3.25,
            'n1-highmem-4' => 6.5,
            'n1-highmem-8' => 13,

            'n1-highcpu-2-d' => 2,
            'n1-highcpu-4-d' => 4,
            'n1-highcpu-8-d' => 8,
            'n1-highcpu-2' => 1.75,
            'n1-highcpu-4' => 3.5,
            'n1-highcpu-8' => 7

            // ADD ICFF

            //ADD RACKSPACE NG
        );

        return $scu[$serverType] ? $scu[$serverType] : 0;
    }

    public function getInfo()
    {
        if ($this->subscriptionId)
        {
            $subscription = $this->chargify->getSubscription($this->subscriptionId);

            if ($subscription['subscription']['trial_ended_at']) {
                $trialsDaysLeft = round((strtotime($subscription['subscription']['trial_ended_at']) - time()) / 86400);
            }

            $retval =  array(
                'state' => ucfirst($subscription['subscription']['state']),
                'nextAssessmentAtTimestamp' => ($subscription['subscription']['next_assessment_at']) ? strtotime($subscription['subscription']['next_assessment_at']) : "",
                'subscriptionActivatedAt' => ($subscription['subscription']['activated_at']) ? strtotime($subscription['subscription']['activated_at']) : "",
                'cancelledAt' => ($subscription['subscription']['canceled_at']) ? strtotime($subscription['subscription']['canceled_at']) : "",
                'createdAt' => ($subscription['subscription']['created_at']) ? strtotime($subscription['subscription']['created_at']) : "",
                'trialDaysLeft' => $trialsDaysLeft,
                'trialEndedAt' => ($subscription['subscription']['trial_ended_at']) ? strtotime($subscription['subscription']['trial_ended_at']) : "",
                'nextAssessmentAt' => ($subscription['subscription']['next_assessment_at']) ? date("M d Y", strtotime($subscription['subscription']['next_assessment_at'])) : "Unknown",
                'ccNumber' => $subscription['subscription']['credit_card']['masked_card_number'],
                'ccType' => ucfirst($subscription['subscription']['credit_card']['card_type']),
                'productHandle' => $subscription['subscription']['product']['handle'],
                'nextAmount' => (int)$subscription['subscription']['product']['price_in_cents']/100,
                'productName' => $subscription['subscription']['product']['name'],
                'productPrice' => (int)$subscription['subscription']['product']['price_in_cents']/100,
                'balance' => -1*(int)$subscription['subscription']['balance_in_cents']/100,
                'couponCode' => $subscription['subscription']['coupon_code'],
                'id' => (int)$subscription['subscription']['id']
            );

            if ($retval['couponCode'])
            {
                try {
                    $coupon = $this->chargify->getCouponDetails($retval['couponCode']);
                    if ($coupon['coupon']['amount_in_cents']) {
                        $discount = (int)$coupon['coupon']['amount_in_cents']/100;
                        $retval['couponDiscount'] = '$' . $discount;
                        if ($coupon['coupon']['recurring']) {
                            $retval['couponDiscount'] .= " / month";
                            $retval['nextAmount'] = $retval['nextAmount'] - $discount;
                        }
                    } elseif ($coupon['coupon']['percentage']) {
                        $retval['couponDiscount'] = $coupon['coupon']['percentage']."%";
                        $discount = round((int)$retval['nextAmount']/100*$coupon['coupon']['percentage']);
                        if ($coupon['coupon']['recurring']) {
                            $retval['couponDiscount'] .= " / month";
                            $retval['nextAmount'] = $retval['nextAmount'] - $discount;
                        }
                    }
                } catch (Exception $e) {}

                $customCoupon = $this->db->GetOne("SELECT id FROM billing.coupons WHERE chargify_coupon_id = ? AND scalr_account_id=?", array(
                    $retval['couponCode'], $this->account->id
                ));
                if ($customCoupon)
                    $retval['couponCode'] = $customCoupon;
            }

            try {
                $components = $this->chargify->getSubscriptionComponents($this->subscriptionId);

                // Scalr Compute Units
                if ($retval['productHandle'] == self::PAY_AS_YOU_GO) {
                    $freeSCUs = 5000;
                } else {
                    $freeSCUs = -1;
                }

                $priceSCU = 0.02;

                $retval['scu'] = array(
                    'usage' => $components[13490]['unit_balance'] > $freeSCUs ? $freeSCUs : $components[13490]['unit_balance'],
                    'limit'  => $freeSCUs,
                    'paid' => $components[13490]['unit_balance'] > $freeSCUs ? $components[13490]['unit_balance']-$freeSCUs : 0,
                    'price' => $priceSCU,
                    'current_usage' => $this->getCurrentScuUsage()
                );

                $retval['scu']['cost'] = round($retval['scu']['paid']*$retval['scu']['price'], 2);

                if ($retval['productHandle'] == self::PAY_AS_YOU_GO) {
                    $retval['nextAmount'] = $retval['nextAmount']+$retval['scu']['cost'];
                }

                //Emerg support
                if ($retval['state'] == 'Active' || $retval['state'] == 'Past_due') {
                    if (in_array($retval['productHandle'], array(self::PACKAGE_WD, self::PACKAGE_MONOPOLY)))
                        $retval['emergSupport'] = 'included';
                    else {
                        $retval['emergSupport'] = $components['6026']['enabled'] ? 'enabled' : 'disabled';
                    }

                    if ($retval['emergSupport'] == 'included' || $retval['emergSupport'] == 'enabled') {
                        $retval['emergPhone'] = '[Emergency phone number: <b>' . \Scalr::config('scalr.billing.emergency_phone_number') . '</b>]';
                    }
                }

                $additionalEnvironments = (int)$components['6027']['allocated_quantity'];
                $retval['environmentsLimit'] = $additionalEnvironments+1;

            } catch (Exception $e) {}


            $retval['isLegacyPlan'] = (in_array($subscription['subscription']['product']['handle'], array(self::PACKAGE_BETA_LEGACY, self::PACKAGE_DEVELOPMENT_LEGACY, self::PACKAGE_MC_LEGACY, self::PACKAGE_PRODUCTION_LEGACY)));
        }


        if ($this->customerId && !$this->subscriptionId) {
            // Development account
            $retval = array(
                'state' => ucfirst('Active'),
                'nextAssessmentAt' => false,
                'ccNumber' => "",
                'ccType' => "",
                'nextAmount' => 0,
                'productName' => "Development (LEGACY)",
                'productPrice' => 0,
                'balance' => 0,
                'isLegacyPlan' => true,
                'id' => false,
                'type' => 'development'
            );
        }

        $retval['realState'] = isset($retval['state']) ? $retval['state'] : null;

        switch ($retval['realState']) {
            case "Active":
                $retval['state'] = 'Subscribed';
                break;

            case "Trialing":
                $retval['state'] = 'Trial';
                break;

            case "Trial_ended":
            case "Canceled":
                $retval['nextAssessmentAt'] = false;
                $retval['state'] = 'Unsubscribed';
                break;

            case "Past_due":
            case "Assessing":
                $retval['state'] = 'Behind on payment';
                break;
        }

        return $retval;
    }

    public function getLimits($package)
    {
        $l = array();
        switch ($package) {
            case self::PACKAGE_SEED:
                $l = array(
                    Scalr_Limits::ACCOUNT_SERVERS => 5
                );
                break;
            case self::PACKAGE_ANGEL:
                $l = array(
                    Scalr_Limits::ACCOUNT_SERVERS => 10,
                    Scalr_Limits::FEATURE_RAID => 1
                );
                break;
            case self::PACKAGE_VC:
                $l = array(
                    Scalr_Limits::ACCOUNT_SERVERS => 20,
                    Scalr_Limits::FEATURE_MONGODB_SHARDING => 1,
                    Scalr_Limits::FEATURE_RAID => 1,
                    Scalr_Limits::FEATURE_CHEF => 1
                );
                break;
            case self::PACKAGE_IPO:
                $l = array(
                    Scalr_Limits::ACCOUNT_SERVERS => 40,
                    Scalr_Limits::FEATURE_2FA => 1,
                    Scalr_Limits::FEATURE_USERS_PERMISSIONS => 1,
                    Scalr_Limits::FEATURE_CHEF => 1,
                    Scalr_Limits::FEATURE_MONGODB_SHARDING => 1,
                    Scalr_Limits::FEATURE_RAID => 1
                );
                break;
            case self::PACKAGE_MONOPOLY:
                $l = array(
                    Scalr_Limits::ACCOUNT_SERVERS => 80,
                    Scalr_Limits::FEATURE_2FA => 1,
                    Scalr_Limits::FEATURE_USERS_PERMISSIONS => 1,
                    Scalr_Limits::FEATURE_CHEF => 1,
                    Scalr_Limits::FEATURE_MONGODB_SHARDING => 1,
                    Scalr_Limits::FEATURE_RAID => 1,
                    Scalr_Limits::FEATURE_MFS => 1
                );
                break;
            case self::PACKAGE_WD:
                $l = array(
                    Scalr_Limits::FEATURE_2FA => 1,
                    Scalr_Limits::FEATURE_USERS_PERMISSIONS => 1,
                    Scalr_Limits::FEATURE_CHEF => 1,
                    Scalr_Limits::FEATURE_MONGODB_SHARDING => 1,
                    Scalr_Limits::FEATURE_RAID => 1,
                    Scalr_Limits::FEATURE_MFS => 1
                );
                break;
            case self::PAY_AS_YOU_GO:
                $l = array(
                    Scalr_Limits::FEATURE_2FA => 1,
                    Scalr_Limits::FEATURE_USERS_PERMISSIONS => 1,
                    Scalr_Limits::FEATURE_CHEF => 1,
                    Scalr_Limits::FEATURE_MONGODB_SHARDING => 1,
                    Scalr_Limits::FEATURE_RAID => 1,
                    Scalr_Limits::FEATURE_MFS => 1
                );
                break;
            case self::PACKAGE_DEVELOPMENT_LEGACY:
                $l = array(
                    Scalr_Limits::ACCOUNT_ENVIRONMENTS => 1,
                    Scalr_Limits::ACCOUNT_USERS => 1,
                    Scalr_Limits::ACCOUNT_FARMS => 1
                );
                break;
            case self::PACKAGE_PRODUCTION_LEGACY:
                $l = array(
                    Scalr_Limits::ACCOUNT_ENVIRONMENTS => 1,
                    Scalr_Limits::ACCOUNT_USERS => 1
                );
                break;
            case self::PACKAGE_BETA_LEGACY:
                $l = array(
                    Scalr_Limits::ACCOUNT_ENVIRONMENTS => 1,
                    Scalr_Limits::ACCOUNT_USERS => 1
                );
                break;
            case self::PACKAGE_MC_LEGACY:
                $l = array(
                    Scalr_Limits::ACCOUNT_ENVIRONMENTS => 1,
                    Scalr_Limits::ACCOUNT_USERS => 1
                );
                break;
        }

        return $l;
    }

    /**
     *
     * @return Scalr_Billing
     */
    public static function init() {
        return new Scalr_Billing();
    }
}
