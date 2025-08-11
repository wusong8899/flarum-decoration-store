<?php

namespace wusong8899\decorationStore\Model;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use wusong8899\decorationStore\Model\DecorationStore;
use Flarum\User\User;

class DecorationStorePurchase extends AbstractModel
{
    use ScopeVisibilityTrait;
    protected $table = 'wusong8899_decoration_store_purchase';

    public function decorationData()
    {
        return $this->hasOne(DecorationStore::class, 'id', 'item_id');
    }

    public function fromUser()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
