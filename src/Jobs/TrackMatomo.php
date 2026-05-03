<?php

namespace FyrnDly\Matomo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FyrnDly\Matomo\MatomoService;

class TrackMatomo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $method;
    protected array $arguments;
    protected array $trackerState;

    /**
     * Create a new job instance.
     */
    public function __construct(string $method, array $arguments, array $trackerState)
    {
        $this->method = $method;
        $this->arguments = $arguments;
        $this->trackerState = $trackerState;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Resolve service from container
        $service = app(MatomoService::class);
        $tracker = $service->getRawTracker();

        // Re-apply the state captured during the request
        if (isset($this->trackerState['ip'])) {
            $tracker->setIp($this->trackerState['ip']);
        }
        if (isset($this->trackerState['user_agent'])) {
            $tracker->setUserAgent($this->trackerState['user_agent']);
        }
        if (isset($this->trackerState['user_id'])) {
            $tracker->setUserId($this->trackerState['user_id']);
        }
        if (isset($this->trackerState['url'])) {
            $tracker->setUrl($this->trackerState['url']);
        }

        if (isset($this->trackerState['lang'])) {
            $tracker->setBrowserLanguage($this->trackerState['lang']);
        }

        // Execute the intended tracking method
        // Map our service method to the internal MatomoTracker method
        $map = [
            'pageView' => 'doTrackPageView',
            'event'    => 'doTrackEvent',
            'goal'     => 'doTrackGoal',
            'search'   => 'doTrackSiteSearch',
        ];

        if (isset($map[$this->method])) {
            call_user_func_array([$tracker, $map[$this->method]], $this->arguments);
        }
    }
}