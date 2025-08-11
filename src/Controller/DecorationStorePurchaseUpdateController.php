<?php

namespace wusong8899\decorationStore\Controller;

use wusong8899\decorationStore\Serializer\DecorationStorePurchaseSerializer;
use wusong8899\decorationStore\Model\DecorationStore;
use wusong8899\decorationStore\Model\DecorationStorePurchase;

use Flarum\User\User;
use Flarum\Api\Controller\AbstractCreateController;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Database\Eloquent\Collection;

class DecorationStorePurchaseUpdateController extends AbstractCreateController
{
    public $serializer = DecorationStorePurchaseSerializer::class;
    public $include = ['decorationData'];
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $requestData = $request->getParsedBody()['data']['attributes'];
        $purchaseID = $requestData['purchaseID'];
        $purchaseStatus = $requestData['purchaseStatus'];
        $currentUserID = $request->getAttribute('actor')->id;
        $errorMessage = "";
        $purchaseData = DecorationStorePurchase::find($purchaseID);

        if (!isset($purchaseData)) {
            $errorMessage = 'wusong8899-decoration-store.lib.save-error';
        } else {
            $itemID = $purchaseData->item_id;
            $userID = $purchaseData->user_id;

            if ($currentUserID !== $userID) {
                $errorMessage = 'wusong8899-decoration-store.lib.save-error';
            } else {
                $itemData = DecorationStore::find($itemID);

                if (isset($itemData)) {
                    $itemType = $itemData->item_type;
                    $itemPurchaseExpired = $purchaseData->is_expired;
                    $itemPropertyImage = "";
                    $purchaseStatus = intval($purchaseStatus) === 1 ? 1 : 0;

                    if ($itemPurchaseExpired === 0) {
                        if ($purchaseStatus === 1) {
                            $itemProperty = json_decode($itemData->item_property);
                            $itemPropertyImage = $itemProperty->image;
                            DecorationStorePurchase::where(["user_id" => $currentUserID, "item_type" => $itemType])->update(['item_status' => 0]);
                        }

                        $purchaseData->item_status = $purchaseStatus;
                        $purchaseData->save();

                        $currentUserData = User::find($currentUserID);
                        $currentUserData["decoration_" . $itemType] = $itemPropertyImage;
                        $currentUserData->save();

                        $include = $this->extractInclude($request);
                        $this->loadRelations(new Collection([$purchaseData]), $include);

                        return $purchaseData;
                    } else {
                        $errorMessage = 'wusong8899-decoration-store.lib.save-error-item-expired';
                    }
                } else {
                    $errorMessage = 'wusong8899-decoration-store.lib.save-error';
                }
            }
        }

        if ($errorMessage !== "") {
            throw new ValidationException(['message' => $this->translator->trans($errorMessage)]);
        }
    }
}
