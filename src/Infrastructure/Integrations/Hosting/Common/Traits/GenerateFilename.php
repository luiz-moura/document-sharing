<?php

namespace App\Infrastructure\Integrations\Hosting\Common\Traits;

trait GenerateFilename
{
    protected function randomString(): string
    {
        return bin2hex(random_bytes(6));
    }

    private function generateFilename(string $filename): string
    {
        $pathInfo = pathinfo($filename);

        return sprintf('/%s-%s.%s', $pathInfo['filename'], $this->randomString(), $pathInfo['extension']);
    }
}
