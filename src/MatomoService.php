<?php

namespace FyrnDly\Matomo;

use MatomoTracker;
use Illuminate\Support\Facades\Auth;
use FyrnDly\Matomo\Jobs\TrackMatomo;

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
     * Store the request state to pass it to the queue.
     */
    protected array $capturedState = [];

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
        
        $this->capturedState['ip'] = $request->ip();
        $this->capturedState['user_agent'] = $request->userAgent();
        $this->capturedState['url'] = $request->fullUrl();
        
        $this->tracker->setIp($this->capturedState['ip']);
        $this->tracker->setUserAgent($this->capturedState['user_agent']);
        $this->tracker->setUrl($this->capturedState['url']);
        
        if ($lang = $request->header('Accept-Language')) {
            $this->capturedState['lang'] = $lang;
            $this->tracker->setBrowserLanguage($lang);
        }

        if (Auth::check()) {
            // Get the attribute name from config, default to 'email'
            $attribute = $this->config['user_id_attribute'] ?? 'email';
            
            // Set the User ID based on the configured attribute
            $this->capturedState['user_id'] = Auth::user()->{$attribute};
            $this->tracker->setUserId($this->capturedState['user_id']);
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
     * Dispatch tracking to queue or execute immediately.
     */
    protected function execute(string $method, array $arguments)
    {
        if ($this->config['queue'] ?? false) {
            TrackMatomo::dispatch($method, $arguments, $this->capturedState);
            return true;
        }

        // Direct execution (Synchronous)
        $map = [
            'pageView' => 'doTrackPageView',
            'event'    => 'doTrackEvent',
            'goal'     => 'doTrackGoal',
            'search'   => 'doTrackSiteSearch',
        ];

        return call_user_func_array([$this->tracker, $map[$method]], $arguments);
    }

    /**
     * Track a page view.
     */
    public function pageView(string $pageTitle, string $customUrl = '')
    {
        if ($customUrl) {
            $this->capturedState['url'] = $customUrl;
            $this->tracker->setUrl($customUrl);
        }
        
        return $this->execute('pageView', [$pageTitle]);
    }

    /**
     * Track a custom event.
     * * If $payload is an array or object, it will be automatically 
     * encoded to JSON and Base64.
     *
     * @param string $category
     * @param string $action
     * @param mixed $payload
     * @param float $value
     * @return mixed
     */
    public function event(string $category, string $action, $payload = '', float $value = 0)
    {
        if (is_array($payload) || is_object($payload)) {
            $payload = base64_encode(json_encode($payload));
        }

        return $this->execute('event', [$category, $action, $payload, $value]);
    }

    /**
     * Track a site search.
     */
    public function search(string $keyword, string $category = '', int $resultsCount = 0)
    {
        return $this->execute('search', [$keyword, $category, $resultsCount]);
    }

    /**
     * Track a conversion/goal.
     */
    public function goal(int $idGoal, float $revenue = 0.0)
    {
        return $this->execute('goal', [$idGoal, $revenue]);
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