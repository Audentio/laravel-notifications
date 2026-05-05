# laravel-notifications

[![Tests](https://github.com/audentio/laravel-notifications/actions/workflows/tests.yml/badge.svg)](https://github.com/audentio/laravel-notifications/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/audentio/laravel-notifications.svg)](https://packagist.org/packages/audentio/laravel-notifications)
[![PHP Version](https://img.shields.io/packagist/php-v/audentio/laravel-notifications.svg)](https://packagist.org/packages/audentio/laravel-notifications)
[![License](https://img.shields.io/packagist/l/audentio/laravel-notifications.svg)](LICENSE)

Notifications and notification preference system for Audentio Laravel platforms. Provides a database-backed notification preference system with per-channel defaults, per-tenant overrides, per-user overrides, a GraphQL API, and optional push notification support.

## Requirements

- PHP 8.1+
- Laravel 12
- [`audentio/laravel-base`](https://github.com/audentio/laravel-base)

## Installation

```bash
composer require audentio/laravel-notifications
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=audentio-notifications-config
php artisan migrate
```

Publish the model stubs:

```bash
php artisan vendor:publish --tag=audentio-notifications-models
```

## Setup

### 1. Implement models

The published stubs in `app/Models/` need to be wired up to your application. At minimum:

**`NotificationPreference`** — extend `getDefaultUserNotificationPreferenceValue()` if needed. The `getName()` method must return a human-readable label (typically from a translation key).

**`NotificationPreferenceGroup`** — same, `getName()` must be implemented.

**`UserNotificationPreference`** — no changes needed beyond the stub.

### 2. Add the trait to your User model

```php
use Audentio\LaravelNotifications\Models\Interfaces\NotifiableUserInterface;
use Audentio\LaravelNotifications\Models\Traits\NotifiableUserTrait;

class User extends Model implements NotifiableUserInterface
{
    use NotifiableUserTrait;

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
}
```

### 3. Create notification handlers

Extend `AbstractNotification` and implement `getNotificationPreferenceId()`:

```php
use Audentio\LaravelNotifications\Notifications\AbstractNotification;

class NewMessageNotification extends AbstractNotification
{
    public function getNotificationPreferenceId(): string
    {
        return 'newMessage';
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->subject('New message');
    }
}
```

### 4. Seed preferences

Run your `NotificationPreferencesAndGroupsTableSeeder` after adding handlers. The seeder auto-discovers handlers and creates `NotificationPreference` records.

## Notification Preference Hierarchy

User preferences follow a 3-level fallback (highest priority first):

| Level | Source | Stored in |
|-------|--------|-----------|
| 3 | User override | `user_notification_preferences.disabled_channels` |
| 2 | Tenant override | App-provided via `getNotificationPreferenceTenantDefaults()` |
| 1 | Global default | `notification_preferences.default_disabled_channels` |

When no record exists at a level, the next level is used. An empty `disabled_channels` array means all channels are enabled.

### Setting global defaults

Set `default_disabled_channels` when seeding a preference:

```php
'streamGoLive' => [
    'available_channels' => ['notification', 'mail'],
    'default_disabled_channels' => ['mail'], // mail off by default for all users
],
```

### Tenant-level overrides

Override `getNotificationPreferenceTenantDefaults()` on your User model to load tenant-specific defaults. This is called once per `getUserNotificationPreferenceValues()` call — load all tenant preferences in a single query:

```php
public function getNotificationPreferenceTenantDefaults(): Collection
{
    $tenant = $this->getCurrentTenant();
    if (!$tenant) {
        return collect();
    }
    return TenantNotificationPreference::where('tenant_id', $tenant->id)->get();
}
```

## Optional: Disable automatic migrations

If you manage migrations manually:

```php
// In a service provider
LaravelNotifications::skipMigrations();
```

## Optional: Push notifications

Enable push support in `config/audentioNotifications.php`:

```php
'push_enabled' => true,
'push_handler_classes' => [
    'expo' => \Audentio\LaravelNotifications\PushHandlers\ExpoPushHandler::class,
    'fcm'  => \Audentio\LaravelNotifications\PushHandlers\FirebasePushHandler::class,
],
```

Run the additional push migrations:

```bash
php artisan migrate
```

Push subscriptions are managed via the `routeNotificationForPush()` method on your User model (provided by `NotifiableUserTrait` — override if needed).

## GraphQL

The package auto-registers a GraphQL schema when `audentio/laravel-graphql` is present. Exposed types, queries, and mutations include:

- **Queries**: `notifications`, `notificationPreferenceGroups`
- **Mutations**: `updateViewerNotificationPreferenceValue`, `dismissNotification`, `markReadNotification`, `markUnreadNotification`, `markReadAllNotifications`, `dismissAllNotifications`
- **Types**: `NotificationPreference`, `NotificationPreferenceGroup`, `UserNotificationPreferenceValue`, `NotificationChannelEnum`

Override individual types via `graphQL_schema_overrides` in the config.

To disable GraphQL registration:

```php
LaravelNotifications::skipGraphQLSchema();
```

## Testing

```bash
composer test
```

## License

MIT
