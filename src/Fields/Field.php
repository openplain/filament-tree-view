<?php

namespace Openplain\FilamentTreeView\Fields;

use Closure;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

abstract class Field
{
    protected string $name;

    protected ?Alignment $alignment = null;

    protected bool|Closure $isHidden = false;

    /**
     * Create a new field instance.
     */
    final public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Create a new field instance.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Get the field name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the field alignment to start (left).
     */
    public function alignStart(): static
    {
        $this->alignment = Alignment::Start;

        return $this;
    }

    /**
     * Set the field alignment to center.
     */
    public function alignCenter(): static
    {
        $this->alignment = Alignment::Center;

        return $this;
    }

    /**
     * Set the field alignment to end (right).
     */
    public function alignEnd(): static
    {
        $this->alignment = Alignment::End;

        return $this;
    }

    /**
     * Set the field alignment.
     */
    public function alignment(Alignment $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    /**
     * Get the field alignment.
     */
    public function getAlignment(): ?Alignment
    {
        return $this->alignment;
    }

    /**
     * Hide the field.
     */
    public function hidden(bool|Closure $condition = true): static
    {
        $this->isHidden = $condition;

        return $this;
    }

    /**
     * Check if the field is hidden.
     */
    public function isHidden(Model $record): bool
    {
        if ($this->isHidden instanceof Closure) {
            return (bool) ($this->isHidden)($record);
        }

        return $this->isHidden;
    }

    /**
     * Get the alignment CSS classes.
     * Returns empty string if no explicit alignment is set.
     */
    public function getAlignmentClass(): string
    {
        if ($this->alignment === null) {
            return '';
        }

        return match ($this->alignment) {
            Alignment::Start => 'text-start',
            Alignment::Center => 'text-center',
            Alignment::End => 'text-end',
        };
    }

    /**
     * Get the flex classes for layout.
     */
    public function getFlexClasses(bool $isFirst): string
    {
        // If field is aligned to end, use ml-auto to push it right
        if ($this->alignment === Alignment::End) {
            return 'flex-shrink-0 ml-auto';
        }

        // First field takes remaining space, others shrink
        return $isFirst ? 'flex-1 min-w-0' : 'flex-shrink-0';
    }

    /**
     * Render the field for the given record.
     */
    abstract public function render(Model|array $record): string;
}
