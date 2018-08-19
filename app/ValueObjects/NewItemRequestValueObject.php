<?php

namespace App\ValueObjects;

use Illuminate\Support\Collection;

/**
 * Class NewItemRequestValueObject
 */
class NewItemRequestValueObject implements ItemRequestValueObjectInterface
{
    /**
     * @var string
     */
    private $action = ItemRequestValueObjectInterface::ACTION_CREATE;

    /**
     * @var string Type of the object.
     */
    private $type;

    /**
     * @var array Array of properties on an object.
     */
    private $properties;

    /**
     * @var array Array of Micropub commands.
     */
    private $commands;

    /**
     * @var array Array of photo URLs.
     */
    private $photos;

    public function __construct(
        string $type,
        array $properties = [],
        array $commands = [],
        array $photos = []
    ) {
        $this->type = $type;
        $this->properties = $properties;
        $this->commands = $commands;
        $this->photos = $photos;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function getMicroformat2(): array
    {
        return [
            'type' => $this->getType(),
            'properties' => $this->getProperties(),
        ];
    }

    /**
     * Get all properties for the item front matter, excluding the content.
     *
     * @return Collection
     */
    public function getFrontMatter(): Collection
    {
        return collect($this->properties)->except('content');
    }

    public function getContent(): string
    {
        return $this->getProperties()['content'] ?? '';
    }

    public function hasPhotos(): bool
    {
        return \count($this->getPhotos()) > 0;
    }
}
