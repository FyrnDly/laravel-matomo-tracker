# Laravel Matomo Tracker
A seamless and lightweight Matomo Analytics integration for Laravel application tracking. This package wraps the [official Matomo PHP Tracker](https://github.com/matomo-org/matomo-php-tracker) to provide a Laravel-friendly syntax, including Facades, auto-configuration, and background processing.

## Features
- **Auto-configuration**: Automatically detects Client IP, User-Agent, and Browser Language.
- **Queue Support**: Dispatch tracking requests to the background for high-performance applications.
- **User Tracking**: Automatically links Matomo User ID with Laravel Auth (configurable attribute).
- **Smart Event Tracking**: Supports strings, arrays, or objects as payloads (automatically encoded).
- **Flexible Tracking**: Supports Page Views, Custom Events, Site Search, and Goals.
- **Custom Dimensions**: Easily set custom dimensions for your tracking data.
- **Laravel Integration**: Includes a Facade and Service Provider with auto-discovery.

## Installation
You can install the package via composer:

```bash
composer require fyrndly/laravel-matomo-tracker
```

The package will automatically register its service provider and facade.

## Configuration
First, publish the configuration file:

```bash
php artisan vendor:publish --tag="matomo-config"
```

Then, add your Matomo details to your `.env` file:

```env
MATOMO_URL=https://your-matomo-domain.com
MATOMO_SITE_ID=1
MATOMO_TOKEN=your_auth_token_here
MATOMO_USER_ID_ATTRIBUTE=email

# Enable Queue for high-traffic apps
MATOMO_QUEUE=false
```

### Configuration Options
- `url`: Your Matomo server URL.
- `site_id`: Your Matomo site ID.
- `token`: Required for manual IP tracking or administrative features.
- `user_id_attribute`: The attribute from the User model to use as the Matomo User ID (e.g., `email`, `id`).
- `queue`: Set to `true` to handle tracking in the background. Requires a working [Laravel Queue](https://laravel.com/docs/queues) worker.

## Usage

### Using the Facade

```php
use FyrnDly\Matomo\Facades\Matomo;

// Track a Page View
Matomo::pageView('Home Page');

// Track a Goal
Matomo::goal(1, 50.0);

// Track Site Search
Matomo::search('search keyword', 'category', 10);
```

### Enhanced Event Tracking
The `event` method supports complex data. If you pass an array or object to the `$payload` parameter, it will be automatically JSON-encoded and Base64-encoded for safe storage in Matomo.

```php
// Simple Event
Matomo::event('Category', 'Action', 'Simple Name');

// Event with Payload (Array/Object)
$data = [
    'user_type' => 'premium',
    'source' => 'referral',
    'step' => 3
];

Matomo::event('Onboarding', 'StepCompleted', $data);
```

### Livewire & Filament Auto-Tracking
You can effortlessly track page views in your Livewire components or Filament pages by simply including the `MatomoTrackView` trait. It hooks into the component's lifecycle and automatically resolves the page title.

```php
use Livewire\Component;
use FyrnDly\Matomo\Traits\MatomoTrackView;

class Dashboard extends Component
{
    use MatomoTrackView;

    public $pageTitle = 'Main Dashboard'; // Automatically tracked as "Go to page Main Dashboard"
}
```
>Note: This trait smartly looks for $pageTitle, $title, $heading properties, or getTitle(), getHeading(), getRecordTitle() methods (perfect for Filament v3).

### High Traffic Handling (Queue)
For high-traffic applications, it is highly recommended to enable the queue to prevent API latency from affecting the user experience.
1. Set `MATOMO_QUEUE=true` in your `.env`.
2. Ensure your `QUEUE_CONNECTION` is not set to `sync`.
3. Run your queue worker: `php artisan queue:work`.


### Custom Dimensions
You can set custom dimensions before tracking:

```php
Matomo::setCustomDimension(1, 'Premium User')
    ->pageView('Dashboard');
```

### Manual User ID
If you need to manually set or override the User ID for a specific request:

```php
Matomo::setUserId('custom-user-id')->pageView('Profile Page');
```

### Accessing the Raw Tracker
If you need to access methods directly from the underlying `MatomoTracker` instance:

```php
$rawTracker = Matomo::getRawTracker();
```

## Credits
- [FyrnDly](https://github.com/FyrnDly)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
