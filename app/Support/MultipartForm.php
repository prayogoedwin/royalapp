<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MultipartForm
{
    public static function mergeIntoRequest(Request $request): void
    {
        $contentType = (string) $request->header('Content-Type');
        if (! preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches)) {
            return;
        }

        $boundary = trim($matches[1] !== '' ? $matches[1] : $matches[2], " \t\"'");
        $raw = $request->getContent();
        if ($boundary === '' || $raw === '') {
            return;
        }

        $blocks = preg_split('/-+'.preg_quote($boundary, '/').'/', $raw) ?: [];

        foreach ($blocks as $block) {
            $block = ltrim($block, "\r\n");
            if ($block === '' || str_starts_with($block, '--')) {
                continue;
            }

            $split = preg_split("/\r\n\r\n/", $block, 2);
            if ($split === false || count($split) < 2) {
                continue;
            }

            [$headers, $body] = $split;
            $body = preg_replace("/\r\n$/", '', $body) ?? $body;

            if (! preg_match('/name="([^"]+)"/', $headers, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];

            if (preg_match('/filename="([^"]*)"/', $headers, $fileMatch)) {
                $filename = $fileMatch[1];
                if ($filename === '') {
                    continue;
                }

                $mime = 'application/octet-stream';
                if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $mimeMatch)) {
                    $mime = trim($mimeMatch[1]);
                }

                $temporaryPath = tempnam(sys_get_temp_dir(), 'put');
                if ($temporaryPath === false) {
                    continue;
                }

                file_put_contents($temporaryPath, $body);
                $request->files->set($name, new UploadedFile($temporaryPath, $filename, $mime, null, true));

                continue;
            }

            $request->request->set($name, is_string($body) ? trim($body) : $body);
        }
    }
}
