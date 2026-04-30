# Laravel Matomo Tracker

A seamless and lightweight Matomo Analytics integration for Laravel application tracking. This package wraps the [official Matomo PHP Tracker](https://github.com/matomo-org/matomo-php-tracker) to provide a Laravel-friendly syntax, including Facades and auto-configuration.

## Features

- **Auto-configuration**: Automatically detects Client IP, User-Agent, and Browser Language.
- **User Tracking**: Automatically links Matomo User ID with Laravel Auth (configurable attribute).
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
```

### Configuration Options

- `url`: Your Matomo server URL.
- `site_id`: Your Matomo site ID.
- `token`: Required for some features like setting the user's IP address manually.
- `user_id_attribute`: The attribute from the authenticated user model to use as the Matomo User ID (e.g., `email`, `id`, or `username`).

## Usage

### Using the Facade

```php
use FyrnDly\\Matomo\\Facades\\Matomo;

// Track a Page View
Matomo::pageView('Home Page');

// Track an Event
Matomo::event('Category', 'Action', 'Name', 1.0);

// Track a Goal
Matomo::goal(1, 50.0);

// Track Site Search
Matomo::search('search keyword', 'category', 10);
```

### Custom Dimensions

You can set custom dimensions before tracking a page view or event:

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