<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class ResourceCollection extends AnonymousResourceCollection
{
    public function withResponse($request, $response)
    {
        $data = $response->getData(true);
        unset($data['links']);
        unset($data['meta']['path']);
        $response->setData($data);
    }
}
