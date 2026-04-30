<?php

namespace FyrnDly\Matomo;

use MatomoTracker;
use Illuminate\Support\Facades\Auth;

class MatomoService
{
    /**
     * The MatomoTracker instance.
     */
    protected MatomoTracker $tracker;

    /**
     * The configuration array.
     */
    protected array $config;

    /**
     * Create a new MatomoService instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->tracker = new MatomoTracker(
            (int) ($config['site_id'] ?? 1), 
            $config['url'] ?? ''
        );

        if (!empty($config['token'])) {
            $this->tracker->setTokenAuth($config['token']);
        }

        $this->autoConfigure();
    }

    /**
     * Automatically configure tracker with request and user data.
     */
    protected function autoConfigure()
    {
        if (app()->runningInConsole()) {
            return;
        }

        $request = request();
        
        $this->tracker->setIp($request->ip());
        $this->tracker->setUserAgent($request->userAgent());
        
        if ($lang = $request->header('Accept-Language')) {
            $this->tracker->setBrowserLanguage($lang);
        }

        if (Auth::check()) {
            // Get the attribute name from config, default to 'email'
            $attribute = $this->config['user_id_attribute'] ?? 'email';
            
            // Set the User ID based on the configured attribute
            $this->tracker->setUserId(Auth::user()->{$attribute}); 
        }
    }

    /**
     * Manually set the User ID.
     *
     * @param string $userId
     * @return $this
     */
    public function setUserId(string $userId)
    {
        $this->tracker->setUserId($userId);
        return $this;
    }

    /**
     * Track a page view.
     */
    public function pageView(string $pageTitle, string $customUrl = '')
    {
        if ($customUrl) {
            $this->tracker->setUrl($customUrl);
        } else {
            $this->tracker->setUrl(request()->fullUrl());
        }
        
        return $this->tracker->doTrackPageView($pageTitle);
    }

    /**
     * Track a custom event.
     */
    public function event(string $category, string $action, string $name = '', float $value = 0)
    {
        return $this->tracker->doTrackEvent($category, $action, $name, $value);
    }

    /**
     * Track a site search.
     */
    public function search(string $keyword, string $category = '', int $resultsCount = 0)
    {
        return $this->tracker->doTrackSiteSearch($keyword, $category, $resultsCount);
    }

    /**
     * Track a conversion/goal.
     */
    public function goal(int $idGoal, float $revenue = 0.0)
    {
        return $this->tracker->doTrackGoal($idGoal, $revenue);
    }

    /**
     * Set a custom dimension value.
     */
    public function setCustomDimension(int $dimensionId, string $value)
    {
        $this->tracker->setCustomDimension($dimensionId, $value);
        return $this;
    }

    /**
     * Access the raw MatomoTracker instance for advanced usage.
     */
    public function getRawTracker(): MatomoTracker
    {
        return $this->tracker;
    }
}