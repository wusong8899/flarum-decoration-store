<?php

namespace wusong8899\decorationStore\Controller;

use Flarum\Frontend\Document;
use Psr\Http\Message\ServerRequestInterface;

class DecorationStoreIndexController
{
    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        return $document;
    }
}
