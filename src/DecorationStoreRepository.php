<?php

namespace wusong8899\decorationStore;

use Illuminate\Database\Eloquent\Builder;

class DecorationStoreRepository
{
    public function query()
    {
        return Model\DecorationStore::query();
    }
}
