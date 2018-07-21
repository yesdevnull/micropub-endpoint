<?php

namespace App\Service;

use Illuminate\Http\UploadedFile;

/**
 * Class MediaService
 */
class MediaService
{
    /**
     * @var string
     */
    private $baseUploadPath;

    public function __construct(string $baseUploadPath)
    {
        $this->baseUploadPath = $baseUploadPath;
    }

    private function getUploadPath(): string
    {
        $currentDate = new \DateTime('now', new \DateTimeZone(env('APP_TIMEZONE')));

        $uploadPath = sprintf(
            '%s/%d/%d/',
            $this->baseUploadPath,
            $currentDate->format('Y'),
            $currentDate->format('m')
        );

        $this->checkFolder($uploadPath);

        return $uploadPath;
    }

    private function getFullyQualifiedUploadPath(string $filename): string
    {
        return app()->storagePath(sprintf(
            'app/%s%s',
            $this->getUploadPath(),
            $filename
        ));
    }

    public function getPublicPathForAsset($uploadedFile): string
    {
        return str_replace($this->baseUploadPath, '', $uploadedFile);
    }

    private function checkFolder($path): bool
    {
        if (file_exists($path)) {
            return true;
        }

        return mkdir($path, 0777, true);
    }

    public function uploadPhotos(array $photos): array
    {
        $uploadedPhotos = [];

        foreach ($photos as $photo) {
            $uploadedPhotos[] = $this->uploadPhoto($photo);
        }

        return $uploadedPhotos;
    }

    public function uploadPhoto(UploadedFile $file): string
    {
        $filename = $file->getClientOriginalName();

        if (file_exists($this->getFullyQualifiedUploadPath($filename))) {
            // If the file exists, try appending a -1 to the end of the file name and start counting up if others exist.
            $incrementor = 1;
            $originalFilename = $this->filenameWithoutExtension($file->getClientOriginalName());

            do {
                $filename = $originalFilename.'-'.$incrementor.'.'.$file->guessClientExtension();

                $incrementor++;
            } while (file_exists($this->getFullyQualifiedUploadPath($filename)));
        }

        $file->storeAs(
            $this->getUploadPath(),
            $filename
        );

        return env('BASE_UPLOAD_URL').$this->getPublicPathForAsset($this->getUploadPath().$filename);
    }

    private function filenameWithoutExtension(string $filename): string
    {
        $filenameArray = explode('.', $filename);
        // Pop the extension value of the array.
        // This is so we can preserve filenames with periods in them (excluding the extension suffix)
        array_pop($filenameArray);

        return implode('', $filenameArray);
    }
}
