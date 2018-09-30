<?php

namespace App\Service;

use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;

/**
 * Class MediaService
 */
class MediaService
{
    /**
     * @var FactoryContract|FilesystemManager
     */
    private $filesystemManager;

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * @var string
     */
    private $baseUploadPath;

    /**
     * MediaService constructor.
     *
     * @param FactoryContract $filesystemManager
     * @param string          $baseUploadPath
     */
    public function __construct(
        FactoryContract $filesystemManager,
        ImageManager $imageManager,
        string $baseUploadPath
    ) {
        $this->filesystemManager = $filesystemManager;
        $this->imageManager = $imageManager;
        $this->baseUploadPath = $baseUploadPath;
    }

    /**
     * Get the URL for the latest media upload.
     *
     * @return string
     */
    public function getLatestUpload(): string
    {
        $now = new \DateTime('now', new \DateTimeZone(env('APP_TIMEZONE')));

        $files = $this->filesystemManager->files($now->format('Y'));

        $filesAndModifiedTimes = [];

        foreach ($files as $file) {
            $filesAndModifiedTimes[$file] = \DateTime::createFromFormat(
                'U',
                $this->filesystemManager->lastModified($file)
            );
        }

        if (0 === \count($files)) {
            return '';
        }

        // Sort from newest to oldest.
        uasort($filesAndModifiedTimes, function ($a, $b) {
            return $a < $b;
        });

        reset($filesAndModifiedTimes);

        $latestFile = key($filesAndModifiedTimes);

        return $this->getFullUrlForAsset($latestFile);
    }

    /**
     * Get the folder for uploading media.
     *
     * @return string
     */
    public function getUploadPath(): string
    {
        $currentDate = new \DateTime(
            'now',
            new \DateTimeZone(env('APP_TIMEZONE'))
        );

        $uploadPath = sprintf(
            '%d/',
            $currentDate->format('Y')
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
     * Return the public URL for the file.
     *
     * @param string $uploadedFile
     *
     * @return string
     */
    public function getFullUrlForAsset(string $uploadedFile): string
    {
        $trimmedFile = $this->getPublicPathForAsset($uploadedFile);

        if ('/' !== $trimmedFile{0}) {
            $trimmedFile = '/'.$trimmedFile;
        }

        return env('BASE_UPLOAD_URL').$trimmedFile;
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
        $filenameAndExtension = str_random(40).'.'.$file->guessExtension();

        $this
            ->imageManager
            ->make($file)
            // Downscale the image to 1200 wide while maintaining the aspect ratio.
            ->resize(1200, null, function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save(
                sprintf(
                    '%s/%s',
                    $this->filesystemManager->path(
                        $this->getUploadPath()
                    ),
                    $filenameAndExtension
                )
            )->destroy();

        $file->storeAs(
            $this->getUploadPath(),
            $filenameAndExtension.'.original'
        );

        return $this->getFullUrlForAsset(
            $this->getUploadPath().$filenameAndExtension
        );
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
        if ($this->filesystemManager->exists($path)) {
            return true;
        }

        return $this->filesystemManager->makeDirectory($path);
    }
}
