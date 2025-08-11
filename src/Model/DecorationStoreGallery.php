<?php

namespace wusong8899\decorationStore\Model;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;

class DecorationStoreGallery extends AbstractModel
{
    use ScopeVisibilityTrait;
    protected $table = 'wusong8899_decoration_store_gallery';
}
