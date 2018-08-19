<?php

namespace App\Parsers;

use App\Service\MediaService;
use App\ValueObjects\ItemRequestValueObjectInterface;
use App\ValueObjects\NewItemRequestValueObject;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

/**
 * Class MicropubRequest
 */
class MicropubRequestParser
{
    private const FLATTEN_KEYS = ['content'];

    private const ENFORCE_ARRAY = ['category', 'tag', 'photo'];

    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function createFromJsonRequest(Request $request): ItemRequestValueObjectInterface
    {
        $parameters = collect($request->json()->all());
        info('json :'.print_r($parameters, true));

        if ($parameters->has('type')) {
            $type = substr(
                head($parameters->get('type')),
                2
            );

            if (!$parameters->has('properties')) {
                throw new BadRequestHttpException('Missing "properties" from request.');
            }

            Assert::isArray($parameters->get('properties'), '"properties" must be an array.');

            $properties = $this->mapProperties($parameters->get('properties'), true);
            $commands = $this->mapCommands($parameters->get('properties'), true);

            $photos = $this->mediaService->uploadPhotos(
                $this->normaliseToArray($request->file('photo', []))
            );

            return new NewItemRequestValueObject(
                $type,
                $properties,
                $commands,
                $photos
            );
        } elseif ($parameters->has('action')) {
            // process actions...
        }

        throw new BadRequestHttpException('Missing "type" or "action" from request.');
    }

    public function createFromFormRequest(Request $request): ItemRequestValueObjectInterface
    {
        $parameters = collect($request->all());
        info('form: '.print_r($parameters, true));

        if ($parameters->has('h')) {
            $type = $parameters->get('h');

            $parameters = $parameters->except(['h', 'access_token']);

            $properties = $this->mapProperties($parameters->toArray());
            $commands = $this->mapCommands($parameters->toArray());

            $photos = $this->mediaService->uploadPhotos(
                $this->normaliseToArray($request->file('photo', []))
            );

            if (array_key_exists('photo', $properties)) {
                // Remove $_FILE upload images from this array as we handle them separately.
                $properties['photo'] = array_filter(
                    $properties['photo'],
                    function ($row) {
                        return !$row instanceof UploadedFile;
                    }
                );
            }

            return new NewItemRequestValueObject(
                $type,
                $properties,
                $commands,
                $photos
            );
        }

        if ($parameters->has('action')) {
            throw new BadRequestHttpException('Modifications to objects must be done over JSON.');
        }

        throw new BadRequestHttpException('Missing "h" parameter.');
    }

    /**
     * Normalises a value to an array.
     *
     * @param mixed $value
     *
     * @return array
     */
    private function normaliseToArray($value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        return [$value];
    }

    private function mapProperties(
        array $properties,
        bool $valuesMustBeArrays = false
    ): array {
        $itemProperties = [];

        foreach ($properties as $propertyName => $propertyValue) {
            if ($valuesMustBeArrays) {
                Assert::isArray($propertyValue, "${propertyName} value must be an array.");
            }

            if (0 !== strpos($propertyName, 'mp-')) {
                if (\is_array($propertyValue) && \in_array($propertyName, self::FLATTEN_KEYS, true)) {
                    $itemProperties[$propertyName] = head($propertyValue);
                } elseif (!\is_array($propertyValue) && \in_array($propertyName, self::ENFORCE_ARRAY, true)) {
                    $itemProperties[$propertyName] = [$propertyValue];
                } else {
                    $itemProperties[$propertyName] = $propertyValue;
                }
            }
        }

        return $itemProperties;
    }

    private function mapCommands(
        array $properties,
        bool $valuesMustBeArrays = false
    ): array {
        $commands = [];

        foreach ($properties as $propertyName => $propertyValue) {
            if ($valuesMustBeArrays) {
                Assert::isArray($propertyValue, "${propertyName} value must be an array.");
            }

            if (0 === strpos($propertyName, 'mp-')) {
                $commands[$propertyName] = $propertyValue;
            }
        }

        return $commands;
    }
}
