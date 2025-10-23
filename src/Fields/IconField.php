<?php

namespace Openplain\FilamentTreeView\Fields;

use Illuminate\Database\Eloquent\Model;

use function Filament\Support\generate_icon_html;

class IconField extends Field
{
    protected string $trueIcon = 'heroicon-o-check-circle';

    protected string $falseIcon = 'heroicon-o-x-circle';

    protected string $trueColor = 'success';

    protected string $falseColor = 'danger';

    /**
     * Configure the field to display as a boolean with icons.
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
     * Set the icon for the true state.
     */
    public function trueIcon(string $icon): static
    {
        $this->trueIcon = $icon;

        return $this;
    }

    /**
     * Set the icon for the false state.
     */
    public function falseIcon(string $icon): static
    {
        $this->falseIcon = $icon;

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
     * Set the color for the true state.
     */
    public function trueColor(string $color): static
    {
        $this->trueColor = $color;

        return $this;
    }

    /**
     * Set the color for the false state.
     */
    public function falseColor(string $color): static
    {
        $this->falseColor = $color;

        return $this;
    }

    /**
     * Render the icon field for the given record.
     */
    public function render(Model|array $record): string
    {
        // Get the field value
        $state = (bool) data_get($record, $this->name);

        // Determine icon and color based on state
        $icon = $state ? $this->trueIcon : $this->falseIcon;
        $color = $state ? $this->trueColor : $this->falseColor;

        // Generate icon HTML
        $iconHtml = generate_icon_html($icon, size: \Filament\Support\Enums\IconSize::Medium);

        // Use Filament's color classes instead of inline styles
        // This respects theme customization and dark mode
        return sprintf(
            '<div class="fi-tree-toggle-icon fi-color-%s">%s</div>',
            $color,
            $iconHtml->toHtml()
        );
    }
}
