<?php

namespace App\ValueObjects;

/**
 * Class MicropubRequestObjectValueObject
 */
class MicropubRequestObjectValueObject
{
    public const ACTION_CREATE = 'CREATE';
    public const ACTION_UPDATE = 'UPDATE';
    public const ACTION_DELETE = 'DELETE';
    public const ACTION_UNDELETE = 'UNDELETE';

    public const TYPE_POST = 'entry';

    private $action;

    /**
     * @var string Type of the object.
     */
    private $type;

    /**
     * @var string URL of the object.
     */
    private $url;

    /**
     * @var array Array of properties on an object.
     */
    private $properties;

    private $commands;

    /**
     * @var array Array of properties to add to an object.
     */
    private $add;

    /**
     * @var array Array of properties to replace in an object.
     */
    private $replace;

    /**
     * @var array Array of properties to delete from an object.
     */
    private $delete;

    public function __construct(
        string $action,
        string $type,
        string $url,
        array $properties = [],
        array $commands = [],
        array $add = [],
        array $replace = [],
        array $delete = []
    ) {
        $this->action = $action;
        $this->type = $type;
        $this->url = $url;
        $this->properties = $properties;
        $this->commands = $commands;
        $this->add = $add;
        $this->replace = $replace;
        $this->delete = $delete;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getAdd(): array
    {
        return $this->add;
    }

    public function getReplace(): array
    {
        return $this->replace;
    }

    public function getDelete(): array
    {
        return $this->delete;
    }

    /**
     * @return array
     */
    public function toMicroformat2(): array
    {
        return [
            'type' => $this->getType(),
            'properties' => $this->getProperties(),
        ];
    }
}
