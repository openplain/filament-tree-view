<?php

namespace Openplain\FilamentTreeView\Fields;

use Closure;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;

class TextField extends Field
{
    protected string|Closure|null $color = null;

    protected ?string $size = null;

    protected ?int $characterLimit = null;

    protected ?string $dimWhenField = null;

    protected mixed $dimWhenValue = null;

    protected string|FontWeight|Closure|null $weight = null;

    protected ?Closure $formatStateUsing = null;

    /**
     * Set the text color.
     * Accepts either a string color name or a Closure that receives the state and record.
     */
    public function color(string|Closure $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set the text size.
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Limit the character length of the text.
     */
    public function limit(int $limit): static
    {
        $this->characterLimit = $limit;

        return $this;
    }

    /**
     * Set the font weight.
     * Accepts either a string ('medium', 'bold', etc.), a Filament FontWeight enum, or a Closure.
     */
    public function weight(string|FontWeight|Closure $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Dim (make semi-transparent) the text when a condition is met.
     *
     * @param  string  $field  The field name to check
     * @param  mixed  $value  The value to compare against (default: false)
     */
    public function dimWhen(string $field, mixed $value = false): static
    {
        $this->dimWhenField = $field;
        $this->dimWhenValue = $value;

        return $this;
    }

    /**
     * Dim the text when the specified field is false (inactive).
     * Shorthand for dimWhen($field, false).
     */
    public function dimWhenInactive(string $field = 'is_active'): static
    {
        return $this->dimWhen($field, false);
    }

    /**
     * Format the state using a custom closure.
     * The closure receives the state value and optionally the record.
     *
     * @param  Closure(mixed, Model|array): string  $callback
     */
    public function formatStateUsing(Closure $callback): static
    {
        $this->formatStateUsing = $callback;

        return $this;
    }

    /**
     * Render the text field for the given record.
     */
    public function render(Model|array $record): string
    {
        // Get the field value
        $state = data_get($record, $this->name);

        // Apply custom formatting if set
        if ($this->formatStateUsing) {
            $formatted = (string) ($this->formatStateUsing)($state, $record);
        } else {
            // Default formatting
            $formatted = (string) $state;
        }

        // Apply character limit if set
        if ($this->characterLimit && strlen($formatted) > $this->characterLimit) {
            $formatted = substr($formatted, 0, $this->characterLimit).'...';
        }

        // Build CSS classes
        $classes = [];

        // Apply size if set, otherwise use default
        if ($this->size) {
            $sizeMap = [
                'xs' => 'text-xs',
                'sm' => 'text-sm',
                'base' => 'text-base',
                'lg' => 'text-lg',
                'xl' => 'text-xl',
            ];
            $classes[] = $sizeMap[$this->size] ?? 'text-sm';
        } else {
            $classes[] = 'text-sm'; // Default Filament text size (14px)
        }

        // Apply color if set
        if ($this->color) {
            $color = $this->color instanceof Closure
                ? ($this->color)($state, $record)
                : $this->color;

            $colorMap = [
                'gray' => 'text-gray-600 dark:text-gray-400',
                'primary' => 'text-primary-600 dark:text-primary-400',
                'success' => 'text-success-600 dark:text-success-400',
                'warning' => 'text-warning-600 dark:text-warning-400',
                'danger' => 'text-danger-600 dark:text-danger-400',
                'info' => 'text-info-600 dark:text-info-400',
            ];
            $classes[] = $colorMap[$color] ?? '';
        }

        // Apply weight if set
        if ($this->weight) {
            $weight = $this->weight instanceof Closure
                ? ($this->weight)($record)
                : $this->weight;

            // Convert FontWeight enum to string value if needed
            if ($weight instanceof FontWeight) {
                $weight = $weight->value;
            }

            $weightMap = [
                'thin' => 'font-thin',
                'extralight' => 'font-extralight',
                'light' => 'font-light',
                'normal' => 'font-normal',
                'medium' => 'font-medium',
                'semibold' => 'font-semibold',
                'bold' => 'font-bold',
                'extrabold' => 'font-extrabold',
                'black' => 'font-black',
            ];
            $classes[] = $weightMap[$weight] ?? '';
        }

        // Apply dimming if condition is met
        $shouldDim = false;
        if ($this->dimWhenField !== null) {
            $fieldValue = data_get($record, $this->dimWhenField);

            // Check if the condition matches
            $shouldDim = $fieldValue === $this->dimWhenValue;
        }

        $classString = implode(' ', array_filter($classes));

        // Use inline style for opacity to ensure it works regardless of Tailwind build
        $style = $shouldDim ? ' style="opacity: 0.4;"' : '';

        if ($classString || $shouldDim) {
            return '<span class="'.$classString.'"'.$style.'>'.e($formatted).'</span>';
        }

        return e($formatted);
    }
}
