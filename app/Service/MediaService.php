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

    /**
     * MediaService constructor.
     *
     * @param string $baseUploadPath
     */
    public function __construct(string $baseUploadPath)
    {
        $this->baseUploadPath = $baseUploadPath;
    }

    /**
     * Get the folder for uploading media.
     *
     * Note: this is relative to storage/app/
     *
     * @return string
     */
    public function getUploadPath(): string
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

    /**
     * Return the upload destination for the file.
     *
     * We append the result of this to the "BASE_UPLOAD_URL" URL.
     *
     * @param string $uploadedFile
     *
     * @return string
     */
    public function getPublicPathForAsset(string $uploadedFile): string
    {
        return str_replace($this->baseUploadPath, '', $uploadedFile);
    }

    /**
     * Upload an array of photos.
     *
     * @param UploadedFile[] $photos
     *
     * @return array
     */
    public function uploadPhotos(array $photos): array
    {
        $uploadedPhotos = [];

        foreach ($photos as $photo) {
            $uploadedPhotos[] = $this->uploadPhoto($photo);
        }

        return $uploadedPhotos;
    }

    /**
     * Save an uploaded file to the media folder and returns its "public" URL.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
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

    /**
     * Returns the name of a file without the extension.
     *
     * @param string $filename
     *
     * @return string
     */
    public function filenameWithoutExtension(string $filename): string
    {
        $filenameArray = explode('.', $filename);
        // Pop the extension value of the array.
        // This is so we can preserve filenames with periods in them (excluding the extension suffix)
        array_pop($filenameArray);

        return implode('', $filenameArray);
    }

    /**
     * Ensure a folder exists and make it if it doesn't.
     *
     * @param string $path
     *
     * @return bool
     */
    private function checkFolder(string $path): bool
    {
        if (file_exists($path)) {
            return true;
        }

        return mkdir($path, 0777, true);
    }

    private function getFullyQualifiedUploadPath(string $filename): string
    {
        return app()->storagePath(sprintf(
            'app/%s%s',
            $this->getUploadPath(),
            $filename
        ));
    }
}
