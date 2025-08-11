<?php

namespace wusong8899\decorationStore\Controller;

use wusong8899\decorationStore\Serializer\DecorationStoreEquipmentSerializer;
use wusong8899\decorationStore\Model\DecorationStorePurchase;

use Flarum\Api\Controller\AbstractListController;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Flarum\Http\UrlGenerator;

class ListDecorationStoreEquipmentController extends AbstractListController
{
    public $serializer = DecorationStoreEquipmentSerializer::class;
    public $include = ['decorationData'];
    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $include = $this->extractInclude($request);
        $actor = $request->getAttribute('actor');
        $userID = $actor->id;

        $decorationStoreEquipmentData = DecorationStorePurchase::where(["item_status" => 1, "user_id" => $userID])->get();

        $this->loadRelations($decorationStoreEquipmentData, $include);

        return $decorationStoreEquipmentData;
    }
}
