<?php

namespace App\Parsers;

use App\ValueObjects\ItemRequestValueObjectInterface;
use App\ValueObjects\NewItemRequestValueObject;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

/**
 * Class MicropubRequest
 */
class MicropubRequestParser
{
    private const FLATTEN_KEYS = ['content', 'photo'];

    public function createFromJsonRequest(Request $request): ItemRequestValueObjectInterface
    {
        $parameters = collect($request->json()->all());

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

            return new NewItemRequestValueObject(
                $type,
                $properties,
                $commands,
                $request->allFiles()
            );
        } elseif ($parameters->has('action')) {
            // process actions...
        }

        throw new BadRequestHttpException('Missing "type" or "action" from request.');
    }

    public function createFromFormRequest(Request $request): ItemRequestValueObjectInterface
    {
        $parameters = collect($request->all());

        if ($parameters->has('h')) {
            $type = $parameters->get('h');

            $parameters = $parameters->except(['h', 'access_token']);

            $properties = $this->mapProperties($parameters->toArray());
            $commands = $this->mapCommands($parameters->toArray());

            return new NewItemRequestValueObject(
                $type,
                $properties,
                $commands,
                $request->allFiles()
            );
        }

        if ($parameters->has('action')) {
            throw new BadRequestHttpException('Modifications to objects must be done over JSON.');
        }

        throw new BadRequestHttpException('Missing "h" parameter.');
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
