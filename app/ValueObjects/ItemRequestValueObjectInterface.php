<?php

namespace App\ValueObjects;

use Illuminate\Support\Collection;

/**
 * Interface ItemRequestValueObjectInterface
 */
interface ItemRequestValueObjectInterface
{
    public const ACTION_CREATE = 'create';

    public function getAction(): string;

    public function getType(): string;

    public function getCommands(): array;

    /**
     * Get all properties for the item front matter, excluding the content.
     *
     * @return Collection
     */
    public function getFrontMatter(): Collection;

    public function getContent(): string;

    public function hasPhotos(): bool;

    public function getPhotos(): array;
}
