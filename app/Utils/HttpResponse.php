<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

trait HttpResponse
{

    protected function success($message = null, $data = null, $code = 200)
    {
        return response()->json([
            'code' => $code,
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }


    protected function error($message = null, $code = 500, $data = null)
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}