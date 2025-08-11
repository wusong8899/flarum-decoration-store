<?php

namespace wusong8899\decorationStore\Controller;

use wusong8899\decorationStore\Serializer\DecorationStorePurchaseSerializer;
use wusong8899\decorationStore\Model\DecorationStore;
use wusong8899\decorationStore\Model\DecorationStorePurchase;
use wusong8899\decorationStore\Helpers\CommonHelper;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Api\Controller\AbstractCreateController;
use Flarum\User\User;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DecorationStorePurchaseController extends AbstractCreateController
{
    public $serializer = DecorationStorePurchaseSerializer::class;
    protected $settings;
    protected $translator;

    public function __construct(Translator $translator, SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
        $this->translator = $translator;
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $requestData = $request->getParsedBody()['data']['attributes'];
        $itemID = $requestData['itemID'];
        $currentUserID = $request->getAttribute('actor')->id;
        $errorMessage = "";

        if (!isset($itemID)) {
            $errorMessage = 'wusong8899-decoration-store.forum.purchase-error';
        } else {
            $itemData = DecorationStore::where(["isActivate" => 1, "id" => $itemID])->first();

            if (isset($itemData)) {
                $itemCount = 1;
                $itemCost = $itemData->item_cost;
                $itemDiscount = $itemData->item_discount;
                $itemActualCost = (new CommonHelper)->getActualCost($itemData, $itemCount, "store");
                $itemSold = $itemData->item_sold;
                $itemAmount = $itemData->item_amount;
                $itemType = $itemData->item_type;
                $itemAvailableAmount = $itemAmount - $itemSold;
                $itemPurchaseType = $itemData->purchase_type;

                if ($itemAvailableAmount >= $itemCount) {
                    $currentUserData = User::find($currentUserID);
                    $currentUserMoneyRemain = $currentUserData->money - ($itemActualCost * $itemCount);

                    if ($currentUserMoneyRemain < 0) {
                        $errorMessage = 'wusong8899-decoration-store.forum.purchase-error-insufficient-fund';
                    } else {
                        $itemPurchaseCount = DecorationStorePurchase::where(["user_id" => $currentUserID, "item_id" => $itemID])->count();

                        if ($itemPurchaseCount === 0) {
                            $itemData->item_sold += $itemCount;
                            $itemData->save();

                            $currentUserData->money = $currentUserMoneyRemain;
                            $currentUserData->save();

                            $settingTimezone = (new CommonHelper)->getSettingTimezone();
                            $expiredDays = 0;

                            if ($itemPurchaseType === "monthly") {
                                $expiredDays = 31;
                            } else if ($itemPurchaseType === "yearly") {
                                $expiredDays = 365;
                            }

                            $itemPurchase = new DecorationStorePurchase();
                            $itemPurchase->item_id = $itemID;
                            $itemPurchase->user_id = $currentUserID;
                            $itemPurchase->item_type = $itemType;
                            $itemPurchase->item_count = $itemCount;
                            $itemPurchase->purchase_type = $itemPurchaseType;
                            $itemPurchase->purchase_cost = $itemCost;
                            $itemPurchase->purchase_discount = $itemDiscount;
                            $itemPurchase->expired_days = $expiredDays;
                            $itemPurchase->assigned_at = Carbon::now($settingTimezone);
                            $itemPurchase->save();

                            return $itemPurchase;
                        } else {
                            $errorMessage = 'wusong8899-decoration-store.forum.purchase-error-item-alreay-have';
                        }
                    }
                } else {
                    $errorMessage = 'wusong8899-decoration-store.forum.sold-out';
                }
            } else {
                $errorMessage = 'wusong8899-decoration-store.forum.purchase-error';
            }
        }

        if ($errorMessage !== "") {
            throw new ValidationException(['message' => $this->translator->trans($errorMessage)]);
        }
    }
}
