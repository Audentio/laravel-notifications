<?php

use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Models\UserWithTenantDefaults;
use Illuminate\Support\Collection;

beforeEach(function () {
    NotificationPreference::resetCache();
});

// ---------------------------------------------------------------------------
// getDefaultUserNotificationPreferenceValue
// ---------------------------------------------------------------------------

it('returns empty disabled_channels when default_disabled_channels is null', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
    ]);

    $default = $preference->getDefaultUserNotificationPreferenceValue();

    expect($default['disabled_channels'])->toBe([]);
});

it('returns default_disabled_channels value from the preference record', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $default = $preference->getDefaultUserNotificationPreferenceValue();

    expect($default['disabled_channels'])->toBe(['mail']);
});

// ---------------------------------------------------------------------------
// getUserNotificationPreferenceValues — global default (level 1)
// ---------------------------------------------------------------------------

it('uses global default when no user or tenant override exists', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = User::create(['email' => 'a@test.com']);

    $values = $user->getUserNotificationPreferenceValues();

    expect($values)->toHaveCount(1)
        ->and($values[0]['notification_preference_id'])->toBe($preference->id)
        ->and($values[0]['disabled_channels'])->toBe(['mail']);
});

it('falls back to empty array when no override and global default is null', function () {
    NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
    ]);

    $user = User::create(['email' => 'a@test.com']);

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe([]);
});

// ---------------------------------------------------------------------------
// getUserNotificationPreferenceValues — user override (level 3)
// ---------------------------------------------------------------------------

it('uses user disabled_channels when a UserNotificationPreference record exists', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = User::create(['email' => 'a@test.com']);
    UserNotificationPreference::create([
        'user_id' => $user->id,
        'notification_preference_id' => $preference->id,
        'disabled_channels' => ['notification'],
    ]);

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe(['notification']);
});

it('user override takes priority over global default', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = User::create(['email' => 'a@test.com']);
    UserNotificationPreference::create([
        'user_id' => $user->id,
        'notification_preference_id' => $preference->id,
        'disabled_channels' => [],
    ]);

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe([]);
});

// ---------------------------------------------------------------------------
// getUserNotificationPreferenceValues — tenant override (level 2)
// ---------------------------------------------------------------------------

it('uses tenant default when no user override but tenant provides one', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = new UserWithTenantDefaults(['email' => 'a@test.com']);
    $user->tenantDefaultsOverride = makeTenantPref($preference->id, ['notification', 'mail']);
    $user->save();

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe(['notification', 'mail']);
});

it('tenant override takes priority over global default', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = new UserWithTenantDefaults(['email' => 'a@test.com']);
    $user->tenantDefaultsOverride = makeTenantPref($preference->id, []);
    $user->save();

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe([]);
});

it('user override takes priority over tenant override', function () {
    $preference = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);

    $user = new UserWithTenantDefaults(['email' => 'a@test.com']);
    $user->tenantDefaultsOverride = makeTenantPref($preference->id, ['notification', 'mail']);
    $user->save();

    UserNotificationPreference::create([
        'user_id' => $user->id,
        'notification_preference_id' => $preference->id,
        'disabled_channels' => ['notification'],
    ]);

    $values = $user->getUserNotificationPreferenceValues();

    expect($values[0]['disabled_channels'])->toBe(['notification']);
});

// ---------------------------------------------------------------------------
// Multiple preferences
// ---------------------------------------------------------------------------

it('returns values for all preferences with correct fallback per preference', function () {
    $prefA = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['mail'],
    ]);
    $prefB = NotificationPreference::create([
        'available_channels' => ['notification', 'mail'],
        'default_disabled_channels' => ['notification'],
    ]);

    $user = User::create(['email' => 'a@test.com']);
    UserNotificationPreference::create([
        'user_id' => $user->id,
        'notification_preference_id' => $prefA->id,
        'disabled_channels' => ['notification'],
    ]);

    $values = collect($user->getUserNotificationPreferenceValues())->keyBy('notification_preference_id');

    expect($values[$prefA->id]['disabled_channels'])->toBe(['notification'])
        ->and($values[$prefB->id]['disabled_channels'])->toBe(['notification']);
});

// ---------------------------------------------------------------------------
// resetCache
// ---------------------------------------------------------------------------

it('resetCache causes next getCached call to re-fetch from the database', function () {
    NotificationPreference::create([
        'available_channels' => ['notification'],
        'default_disabled_channels' => ['notification'],
    ]);

    $cached = NotificationPreference::getCached();
    expect($cached)->toHaveCount(1);

    NotificationPreference::create([
        'available_channels' => ['mail'],
    ]);

    // Cache still returns stale count
    expect(NotificationPreference::getCached())->toHaveCount(1);

    NotificationPreference::resetCache();

    // After reset, fresh count
    expect(NotificationPreference::getCached())->toHaveCount(2);
});

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function makeTenantPref(string $notificationPreferenceId, array $disabledChannels): Collection
{
    $obj = new stdClass();
    $obj->notification_preference_id = $notificationPreferenceId;
    $obj->disabled_channels = $disabledChannels;

    return collect([$obj]);
}
