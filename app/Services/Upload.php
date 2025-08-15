<?php
declare(strict_types=1);

namespace App\Services;

use Psr\Http\Message\UploadedFileInterface;

class Upload
{
    public function upload(UploadedFileInterface $file, string $directory): string
    {
        $filename = bin2hex(random_bytes(8)) . '-' . preg_replace('/[^a-zA-Z0-9.]/', '', $file->getClientFilename());
        $file->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }
}
