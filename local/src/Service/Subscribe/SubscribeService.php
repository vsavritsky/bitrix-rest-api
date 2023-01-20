<?php

namespace App\Service\Subscribe;

use CModule;
use CSubscription;
use CUser;

class SubscribeService
{
    public function __construct()
    {
        CModule::IncludeModule('subscribe');
    }

    public function addSubscribe($email)
    {
        $_REQUEST['licenses_subscribe'] = 'Y';

        $userId = null;
        $filter = ['EMAIL' => $email];
        $elementsResult = CUser::GetList(($by = "ID"), ($order = "ASC"), $filter);
        if ($rsUser = $elementsResult->Fetch()) {
            $userId = $rsUser['ID'];
        }

        $subscription = CSubscription::GetByEmail($email);
        $subscriptionItem = $subscription->Fetch();

        $add = false;

        if ($subscriptionItem) {
            if ($subscriptionItem['CONFIRMED'] == 'Y') {
                $subscr = new CSubscription();
                $subscr->Update($subscriptionItem['ID'], ['ACTIVE' => 'Y'], "s1");
            } else {
                $res = CSubscription::Delete($subscriptionItem['ID']);
                $add = true;
            }
        } elseif (!$subscriptionItem) {
            $add = true;
        }

        if ($add) {
            $subscribeFields = [
                "USER_ID" => $userId,
                "FORMAT" => "html",
                "EMAIL" => $email,
                "ACTIVE" => "Y",
                "SEND_CONFIRM" => "Y",
                "RUB_ID" => [1],
                "ALL_SITES" => "Y",
            ];

            $subscr = new CSubscription();
            $ID = $subscr->Add($subscribeFields, "s1");

            return $ID;
        }

        return null;
    }

    public function removeSubscribe($email)
    {
        $_REQUEST['licenses_subscribe'] = 'Y';

        $userId = null;
        $filter = ['EMAIL' => $email];
        $elementsResult = CUser::GetList(($by = "ID"), ($order = "ASC"), $filter);
        if ($rsUser = $elementsResult->Fetch()) {
            $userId = $rsUser['ID'];
        }

        $subscription = CSubscription::GetByEmail($email);
        $subscriptionItem = $subscription->Fetch();
        if ($subscriptionItem) {
            $subscr = new CSubscription();
            $subscr->Update($subscriptionItem['ID'], ['ACTIVE' => 'N'], "s1");

            return true;
        }

        return null;
    }

    public function confirmed($id, $code)
    {
        $subscription = CSubscription::GetByID($id);
        $subscriptionItem = $subscription->Fetch();

        if ($subscriptionItem['CONFIRM_CODE'] == $code) {
            $subscr = new CSubscription();
            $subscr->Update($id, ['ACTIVE' => 'Y', "CONFIRMED" => "Y"], "s1");
        }
    }
}
