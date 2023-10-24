<?php

namespace App\Helpers;

use App\Common\ResourceConst;
use Symfony\Component\HttpFoundation\Request;

class CommonHelper
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public static function getDataFromHeaderRequest(Request $request, $key): string
    {
        return $request->headers->get($key);
    }


    public static function removeNullValue($data): array
    {
        return array_filter($data, fn($value) => !is_null($value));
    }
}
