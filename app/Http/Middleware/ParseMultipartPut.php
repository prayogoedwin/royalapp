<?php

namespace App\Http\Middleware;

use App\Support\MultipartForm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartPut
{
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->getMethod();
        $contentType = (string) $request->header('Content-Type');

        if (
            in_array($method, ['PUT', 'PATCH'], true)
            && str_contains($contentType, 'multipart/form-data')
            && $request->request->count() === 0
            && $request->allFiles() === []
        ) {
            MultipartForm::mergeIntoRequest($request);
        }

        return $next($request);
    }
}
