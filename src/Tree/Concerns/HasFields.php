<?php

namespace Openplain\FilamentTreeView\Tree\Concerns;

use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Fields\Field;

trait HasFields
{
    /**
     * @var array<Field>
     */
    protected array $fields = [];

    /**
     * Set the fields to display in the tree.
     *
     * @param  array<Field>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the fields to display in the tree.
     *
     * @return array<Field>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Check if the tree has custom fields defined.
     */
    public function hasFields(): bool
    {
        return count($this->fields) > 0;
    }

    /**
     * Get visible fields for a specific record.
     *
     * @param  Model|array  $record
     * @return array<Field>
     */
    public function getVisibleFields(Model | array $record): array
    {
        return array_filter(
            $this->fields,
            fn (Field $field) => ! $field->isHidden($record)
        );
    }
}
