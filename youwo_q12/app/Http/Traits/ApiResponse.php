<?php

namespace App\Http\Traits;

trait ApiResponse
{
    protected function successResponse($message = 'Success', $data = [], $code = 200)
    {
        $response = [
            'response' => 'ok',
            'message' => $message
        ];
        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $errData = [])
    {
        $response = [
            'response' => 'error',
            'message' => $message
        ];
        if (!empty($errData)) {
            $response['ErrorData'] = $errData;
        }
        return response()->json($response, $code);
    }
}