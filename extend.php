<?php

use Flarum\Extend;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\User\User;
use Flarum\Foundation\Paths;
use Flarum\Http\UrlGenerator;

use wusong8899\decorationStore\Console\DecorationStoreScheduleCommand;
use wusong8899\decorationStore\Console\PublishSchedule;
use wusong8899\decorationStore\Controller\DecorationStoreIndexController;
use wusong8899\decorationStore\Controller\ListDecorationStoreController;
use wusong8899\decorationStore\Controller\DecorationStoreSortController;
use wusong8899\decorationStore\Controller\DecorationStoreUpdateController;
use wusong8899\decorationStore\Controller\DecorationStoreAddController;
use wusong8899\decorationStore\Controller\DecorationStoreUploadImageController;

use wusong8899\decorationStore\Controller\DecorationStorePurchaseController;
use wusong8899\decorationStore\Controller\DecorationStorePurchaseUpdateController;
use wusong8899\decorationStore\Controller\DecorationStorePurchaseSubscriptionController;
use wusong8899\decorationStore\Controller\ListDecorationStorePurchaseHisotryController;
use wusong8899\decorationStore\Controller\ListDecorationStoreEquipmentController;
use wusong8899\decorationStore\Controller\ListDecorationStoreGalleryController;

use wusong8899\decorationStore\Serializer\DecorationStorePurchaseSerializer;

use wusong8899\decorationStore\Search\DecorationStoreSearcher;
use wusong8899\decorationStore\Search\DecorationStoreGambit;
use wusong8899\decorationStore\Search\DecorationStoreFullTextGambit;
use wusong8899\decorationStore\Notification\DecorationSubscriptionBlueprint;

$extend = [
    (new Extend\Frontend('admin'))->js(__DIR__ . '/js/dist/admin.js')->css(__DIR__ . '/less/admin.less'),
    (new Extend\Frontend('forum'))->js(__DIR__ . '/js/dist/forum.js')->css(__DIR__ . '/less/forum.less')
        ->route('/decorationStore', 'decorationStore.index', DecorationStoreIndexController::class),

    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\Routes('api'))
        ->get('/decorationStoreList', 'decorationStore.get', ListDecorationStoreController::class)
        ->post('/decorationStoreList', 'decorationStore.add', DecorationStoreAddController::class)
        ->post('/decorationStoreList/order', 'decorationStore.order', DecorationStoreSortController::class)
        ->patch('/decorationStoreList/{id}', 'decorationStore.update', DecorationStoreUpdateController::class)
        ->post('/decorationItemImageUpload', 'decorationStore.itemImageUpload', DecorationStoreUploadImageController::class)

        ->post('/decorationStorePurchase', 'decorationStore.purchase', DecorationStorePurchaseController::class)
        ->post('/decorationStorePurchase/subscription', 'decorationStore.subscription', DecorationStorePurchaseSubscriptionController::class)
        ->patch('/decorationStorePurchase/{purchase_id}', 'decorationStore.purchaseUpdate', DecorationStorePurchaseUpdateController::class)
        ->get('/decorationStorePurchaseHistory', 'decorationStore.purchaseHistory', ListDecorationStorePurchaseHisotryController::class)
        ->get('/decorationStoreEquipment', 'decorationStore.equipment', ListDecorationStoreEquipmentController::class)
        ->patch('/decorationStoreEquipment/{purchase_id}', 'decorationStore.equipmentUpdate', DecorationStorePurchaseUpdateController::class)
        ->get('/decorationStoreGallery', 'decorationStore.gallery', ListDecorationStoreGalleryController::class),

    (new Extend\ApiSerializer(BasicUserSerializer::class))
        ->attribute('decorationAvatarFrame', function (BasicUserSerializer $serializer, User $user) {
            return $user->decoration_avatarFrame;
        })->attribute('decorationProfileBackground', function (BasicUserSerializer $serializer, User $user) {
            return $user->decoration_profileBackground;
        })->attribute('decorationUsernameColor', function (BasicUserSerializer $serializer, User $user) {
            return $user->decoration_usernameColor;
        }),

    (new Extend\Settings())
        ->default('wusong8899-decoraton-store.decorationStoreItemTypes', ['avatarFrame', 'profileBackground', 'usernameColor'])
        ->default('wusong8899-decoraton-store.decorationStoreTimezone', 'Asia/Shanghai')
        ->serializeToForum('decorationStoreDisplayName', 'wusong8899-decoraton-store.decorationStoreDisplayName', 'strval')
        ->serializeToForum('decorationStoreTimezone', 'wusong8899-decoraton-store.decorationStoreTimezone')
        ->serializeToForum('decorationStoreItemTypes', 'wusong8899-decoraton-store.decorationStoreItemTypes'),

    (new Extend\SimpleFlarumSearch(DecorationStoreSearcher::class))
        ->addGambit(DecorationStoreGambit::class)
        ->setFullTextGambit(DecorationStoreFullTextGambit::class),

    (new Extend\Filesystem())
        ->disk('wusong8899-decoration-store', function (Paths $paths, UrlGenerator $url) {
            return [
                'root' => "$paths->public/assets/decorationStore",
                'url' => $url->to('forum')->path('assets/decorationStore')
            ];
        }),

    (new Extend\Console())
        ->command(DecorationStoreScheduleCommand::class)
        ->schedule(DecorationStoreScheduleCommand::class, new PublishSchedule()),

    (new Extend\Notification())
        ->type(DecorationSubscriptionBlueprint::class, DecorationStorePurchaseSerializer::class, ['alert']),
];

return $extend;