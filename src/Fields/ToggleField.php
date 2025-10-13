<?php

namespace Openplain\FilamentTreeView\Fields;

use Illuminate\Database\Eloquent\Model;
use function Filament\Support\generate_icon_html;

class ToggleField extends Field
{
    protected string $trueIcon = 'heroicon-o-check-circle';

    protected string $falseIcon = 'heroicon-o-x-circle';

    protected string $trueColor = 'success';

    protected string $falseColor = 'danger';

    /**
     * Configure the field to display as a boolean toggle.
     * This is the default behavior, but can be called explicitly.
     */
    public function boolean(): static
    {
        return $this;
    }

    /**
     * Set custom icons for true/false states.
     */
    public function icons(string $trueIcon, string $falseIcon): static
    {
        $this->trueIcon = $trueIcon;
        $this->falseIcon = $falseIcon;

        return $this;
    }

    /**
     * Set custom colors for true/false states.
     */
    public function colors(string $trueColor, string $falseColor): static
    {
        $this->trueColor = $trueColor;
        $this->falseColor = $falseColor;

        return $this;
    }

    /**
     * Render the toggle field for the given record.
     */
    public function render(Model | array $record): string
    {
        // Get the field value
        $state = (bool) data_get($record, $this->name);

        // Determine icon and color based on state
        $icon = $state ? $this->trueIcon : $this->falseIcon;
        $color = $state ? $this->trueColor : $this->falseColor;

        // Use Filament's generate_icon_html with inline color style
        $colorMap = [
            'success' => 'rgb(34, 197, 94)', // green-500
            'danger' => 'rgb(239, 68, 68)', // red-500
            'warning' => 'rgb(245, 158, 11)', // amber-500
            'info' => 'rgb(59, 130, 246)', // blue-500
            'gray' => 'rgb(107, 114, 128)', // gray-500
            'primary' => 'rgb(99, 102, 241)', // indigo-500
        ];

        $colorValue = $colorMap[$color] ?? $colorMap['gray'];

        // Generate icon HTML with custom styling
        $iconHtml = generate_icon_html($icon, size: \Filament\Support\Enums\IconSize::Medium);

        return '<div class="fi-tree-toggle-icon" style="color: ' . $colorValue . ';">' .
            $iconHtml->toHtml() .
            '</div>';
    }
}
