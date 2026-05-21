<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\User;

class AdminSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function payload(User $user): array
    {
        $settings = $this->resolve($user);

        return [
            'id' => $settings->id,
            'store_name' => $settings->store_name,
            'support_email' => $settings->support_email,
            'support_phone' => $settings->support_phone,
            'low_stock_threshold' => (int) $settings->low_stock_threshold,
            'dashboard_refresh_seconds' => (int) $settings->dashboard_refresh_seconds,
            'order_auto_confirm' => (bool) $settings->order_auto_confirm,
            'send_daily_summary' => (bool) $settings->send_daily_summary,
            'maintenance_mode' => (bool) $settings->maintenance_mode,
            'notes' => $settings->notes,
            'updated_at' => optional($settings->updated_at)->toISOString(),
        ];
    }

    public function update(User $user, array $attributes): AdminSetting
    {
        $settings = $this->resolve($user);

        $settings->update([
            'store_name' => $attributes['store_name'],
            'support_email' => $attributes['support_email'] ?? null,
            'support_phone' => $attributes['support_phone'] ?? null,
            'low_stock_threshold' => $attributes['low_stock_threshold'],
            'dashboard_refresh_seconds' => $attributes['dashboard_refresh_seconds'],
            'order_auto_confirm' => $attributes['order_auto_confirm'] ?? false,
            'send_daily_summary' => $attributes['send_daily_summary'] ?? false,
            'maintenance_mode' => $attributes['maintenance_mode'] ?? false,
            'notes' => $attributes['notes'] ?? null,
        ]);

        return $settings->refresh();
    }

    public function resolve(User $user): AdminSetting
    {
        return $user->adminSetting()->firstOrCreate(
            [],
            [
                'store_name' => 'Heritage Harvest',
                'support_email' => $user->email,
                'support_phone' => $user->phone,
                'low_stock_threshold' => 5,
                'dashboard_refresh_seconds' => 60,
                'order_auto_confirm' => false,
                'send_daily_summary' => true,
                'maintenance_mode' => false,
                'notes' => null,
            ],
        );
    }
}
