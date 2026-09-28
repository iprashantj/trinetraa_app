<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Standard success envelope — mirrors the Next.js `{ data, meta }` shape.
     */
    protected function data($data, array $meta = null, int $status = 200): JsonResponse
    {
        $payload = ['data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }

    protected function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    protected function notFound(string $message = 'Not found.'): JsonResponse
    {
        return response()->json(['message' => $message], 404);
    }
}
