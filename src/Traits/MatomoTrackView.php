<?php

namespace FyrnDly\Matomo\Traits;

use FyrnDly\Matomo\Facades\Matomo;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @method string|Htmlable|null getTitle()
 * @method string|Htmlable|null getLabel()
 * @method string|Htmlable|null getHeading()
 * @method string|Htmlable|null getRecordTitle()
 * @property string|null $pageTitle
 * @property string|null $title
 * @property string|null $heading
 */
trait MatomoTrackView
{
    /**
     * Automatically invoked by Livewire during the component's mount lifecycle.
     * Resolves the page label and sends the page view tracking event to Matomo.
     *
     * @return void
     */
    public function mountMatomoTrackView(): void
    {
        $label = $this->resolveMatomoPageLabel();
        Matomo::pageView(__("Go to page :page", ['page' => $label]));
    }

    /**
     * Safely resolve the appropriate page label from the component's properties or methods.
     * Prioritizes public properties first, followed by specific getter methods.
     *
     * @return string
     */
    protected function resolveMatomoPageLabel(): string
    {
        // 1. Check for defined public properties
        $properties = ['pageTitle', 'title', 'heading'];
        foreach ($properties as $property) {
            if (property_exists($this, $property) && !empty($this->{$property})) {
                $rawLabel = $this->{$property};
                return $this->cleanMatomoLabel($rawLabel);
            }
        }

        // 2. Check for defined methods (Useful for Filament dynamic pages)
        $methods = ['getTitle', 'getLabel', 'getHeading', 'getRecordTitle'];
        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $result = $this->{$method}();
                if (! empty($result)) {
                    return $this->cleanMatomoLabel($result);
                }
            }
        }

        // 3. Fallback default label by using the application name
        return config('app.name', 'Laravel');
    }

    /**
     * Sanitize the resolved label by converting Htmlable objects to strings
     * and stripping out any HTML tags to ensure clean tracking logs.
     *
     * @param mixed $rawLabel The raw label which could be a string or an Htmlable instance.
     * @return string
     */
    protected function cleanMatomoLabel($rawLabel): string
    {
        $stringLabel = $rawLabel instanceof Htmlable 
            ? $rawLabel->toHtml() 
            : (string) $rawLabel;

        return strip_tags($stringLabel);
    }
}