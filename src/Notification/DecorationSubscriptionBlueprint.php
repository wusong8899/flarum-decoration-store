<?php

namespace wusong8899\decorationStore\Notification;

use Flarum\Notification\Blueprint\BlueprintInterface;
use Illuminate\Support\Carbon;

use wusong8899\decorationStore\Model\DecorationStorePurchase;
use wusong8899\decorationStore\Helpers\CommonHelper;

class DecorationSubscriptionBlueprint implements BlueprintInterface
{
    public $decorationStorePurchase;

    public function __construct(DecorationStorePurchase $decorationStorePurchase)
    {
        $this->decorationStorePurchase = $decorationStorePurchase;
    }

    public function getSubject()
    {
        return $this->decorationStorePurchase;
    }

    public function getFromUser()
    {
        return $this->decorationStorePurchase->fromUser;
    }

    public function getData()
    {
        $settingTimezone = (new CommonHelper)->getSettingTimezone();

        return ['title' => $this->decorationStorePurchase->decorationData->item_title, 'expired' => $this->decorationStorePurchase->is_expired, 't' => Carbon::now($settingTimezone)->timestamp];
    }

    public static function getType()
    {
        return 'decorationStorePurchase';
    }

    public static function getSubjectModel()
    {
        return DecorationStorePurchase::class;
    }
}
