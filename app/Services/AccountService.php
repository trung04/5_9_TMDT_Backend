<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountService
{
    /**
     * @return array<string, mixed>
     */
    public function profilePayload(User $user): array
    {
        $user->loadMissing(['addresses', 'rewardRedemptions']);

        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'favorite_region' => $user->favorite_region,
            'avatar' => $user->avatar_url,
            'member_since' => optional($user->created_at)->toISOString(),
            'newsletter' => (bool) $user->newsletter,
            'sms_alerts' => (bool) $user->sms_alerts,
            'order_email' => (bool) $user->order_email,
            'security_alerts' => (bool) $user->security_alerts,
            'addresses' => $user->addresses
                ->sortByDesc(fn (UserAddress $address) => $address->is_default)
                ->values()
                ->map(fn (UserAddress $address): array => $this->addressPayload($address))
                ->all(),
            'reward_snapshot' => [
                'tier' => $user->reward_tier,
                'points' => (int) $user->reward_points,
                'next_tier_points' => (int) $user->next_tier_points,
                'perks' => $this->rewardPerks($user->reward_tier),
            ],
            'reward_history' => $user->rewardRedemptions
                ->sortByDesc('created_at')
                ->values()
                ->map(fn (RewardRedemption $item): array => $this->rewardPayload($item))
                ->all(),
        ];
    }

    public function updateProfile(User $user, array $attributes): User
    {
        $user->update([
            'full_name' => $attributes['name'] ?? $user->full_name,
            'phone' => $attributes['phone'] ?? $user->phone,
            'address' => $attributes['address'] ?? $user->address,
            'city' => $attributes['city'] ?? $user->city,
            'favorite_region' => $attributes['favorite_region'] ?? $user->favorite_region,
            'avatar_url' => $attributes['avatar'] ?? $user->avatar_url,
            'newsletter' => $attributes['newsletter'] ?? $user->newsletter,
            'sms_alerts' => $attributes['sms_alerts'] ?? $user->sms_alerts,
            'order_email' => $attributes['order_email'] ?? $user->order_email,
            'security_alerts' => $attributes['security_alerts'] ?? $user->security_alerts,
        ]);

        return $user->refresh();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password_hash' => Hash::make($newPassword),
        ]);
    }

    public function createAddress(User $user, array $attributes): UserAddress
    {
        return DB::transaction(function () use ($user, $attributes): UserAddress {
            if (! $user->addresses()->exists()) {
                $attributes['is_default'] = true;
            }

            if (! empty($attributes['is_default'])) {
                $this->clearDefaultAddress($user);
            }

            $address = $user->addresses()->create($attributes);

            if ($address->is_default) {
                $this->syncDefaultAddressToProfile($user, $address);
            }

            return $address->refresh();
        });
    }

    public function updateAddress(User $user, UserAddress $address, array $attributes): UserAddress
    {
        $this->assertAddressOwner($user, $address);

        return DB::transaction(function () use ($user, $address, $attributes): UserAddress {
            if (! empty($attributes['is_default'])) {
                $this->clearDefaultAddress($user);
            }

            $address->update($attributes);

            if ($address->is_default) {
                $this->syncDefaultAddressToProfile($user, $address->refresh());
            }

            return $address->refresh();
        });
    }

    public function deleteAddress(User $user, UserAddress $address): void
    {
        $this->assertAddressOwner($user, $address);

        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->orderByDesc('id')->first();

                if ($next) {
                    $next->update(['is_default' => true]);
                    $this->syncDefaultAddressToProfile($user, $next->refresh());
                }
            }
        });
    }

    public function setDefaultAddress(User $user, UserAddress $address): UserAddress
    {
        $this->assertAddressOwner($user, $address);

        return DB::transaction(function () use ($user, $address): UserAddress {
            $this->clearDefaultAddress($user);
            $address->update(['is_default' => true]);
            $this->syncDefaultAddressToProfile($user, $address->refresh());

            return $address->refresh();
        });
    }

    public function listNotifications(User $user): array
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Notification $notification): array => $this->notificationPayload($notification))
            ->all();
    }

    public function markNotificationRead(User $user, Notification $notification): Notification
    {
        if ($notification->user_id !== $user->id) {
            abort(404, 'Notification not found.');
        }

        if (! $notification->read_at) {
            $notification->update([
                'status' => Notification::STATUS_READ,
                'read_at' => now(),
            ]);
        }

        return $notification->refresh();
    }

    public function listComplaints(User $user): array
    {
        return Complaint::query()
            ->where('user_id', $user->id)
            ->with(['order', 'product', 'resolver'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Complaint $complaint): array => $this->complaintPayload($complaint))
            ->all();
    }

    public function createComplaint(User $user, array $attributes): Complaint
    {
        $order = Order::query()
            ->where('id', $attributes['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => ['Order does not belong to the authenticated user.'],
            ]);
        }

        $product = Product::query()->find($attributes['product_id']);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => ['Product not found.'],
            ]);
        }

        return Complaint::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'reason' => $attributes['reason'],
            'content' => $attributes['content'],
            'image_url' => $attributes['image_url'] ?? null,
            'status' => Complaint::STATUS_OPEN,
        ])->load(['order', 'product', 'resolver']);
    }

    public function redeemReward(User $user, string $title, int $pointsCost): User
    {
        if ($pointsCost > (int) $user->reward_points) {
            throw ValidationException::withMessages([
                'points_cost' => ['Insufficient reward points.'],
            ]);
        }

        return DB::transaction(function () use ($user, $title, $pointsCost): User {
            $user->update([
                'reward_points' => max(0, (int) $user->reward_points - $pointsCost),
            ]);

            $user->rewardRedemptions()->create([
                'title' => $title,
                'points_used' => $pointsCost,
                'status' => RewardRedemption::STATUS_COMPLETED,
            ]);

            return $user->refresh()->load(['addresses', 'rewardRedemptions']);
        });
    }

    /**
     * @return array{product_ids:list<int>, products:list<array<string, mixed>>}
     */
    public function listWishlist(User $user): array
    {
        $items = $user->wishlistItems()
            ->with(['product.category', 'product.supplier'])
            ->orderByDesc('created_at')
            ->get();

        $products = $items
            ->map(fn (WishlistItem $item) => $item->product)
            ->filter()
            ->values();

        return [
            'product_ids' => $products
                ->map(fn (Product $product): int => (int) $product->id)
                ->all(),
            'products' => $products
                ->map(fn (Product $product): array => $product->toArray())
                ->all(),
        ];
    }

    /**
     * @return array{product_ids:list<int>, products:list<array<string, mixed>>}
     */
    public function addWishlistItem(User $user, Product $product): array
    {
        $user->wishlistItems()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return $this->listWishlist($user->refresh());
    }

    /**
     * @return array{product_ids:list<int>, products:list<array<string, mixed>>}
     */
    public function removeWishlistItem(User $user, Product $product): array
    {
        $user->wishlistItems()
            ->where('product_id', $product->id)
            ->delete();

        return $this->listWishlist($user->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function notificationPayload(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'channel' => $notification->channel,
            'status' => $notification->status,
            'sent_at' => optional($notification->sent_at)->toISOString(),
            'read_at' => optional($notification->read_at)->toISOString(),
            'created_at' => optional($notification->created_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function complaintPayload(Complaint $complaint): array
    {
        return [
            'id' => $complaint->id,
            'reason' => $complaint->reason,
            'content' => $complaint->content,
            'image_url' => $complaint->image_url,
            'status' => $complaint->status,
            'resolution_note' => $complaint->resolution_note,
            'created_at' => optional($complaint->created_at)->toISOString(),
            'order' => $complaint->order ? [
                'id' => $complaint->order->id,
                'order_no' => $complaint->order->order_no,
                'status' => $complaint->order->status,
                'total_amount' => $complaint->order->total_amount,
            ] : null,
            'product' => $complaint->product ? [
                'id' => $complaint->product->id,
                'name' => $complaint->product->name,
                'sku' => $complaint->product->sku,
            ] : null,
            'resolver' => $complaint->resolver ? [
                'id' => $complaint->resolver->id,
                'full_name' => $complaint->resolver->full_name,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function addressPayload(UserAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient' => $address->recipient,
            'phone' => $address->phone,
            'line1' => $address->line1,
            'city' => $address->city,
            'note' => $address->note,
            'is_default' => (bool) $address->is_default,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rewardPayload(RewardRedemption $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'points_used' => $item->points_used,
            'status' => $item->status,
            'created_at' => optional($item->created_at)->toISOString(),
        ];
    }

    private function assertAddressOwner(User $user, UserAddress $address): void
    {
        if ($address->user_id !== $user->id) {
            abort(404, 'Address not found.');
        }
    }

    private function clearDefaultAddress(User $user): void
    {
        $user->addresses()->where('is_default', true)->update(['is_default' => false]);
    }

    private function syncDefaultAddressToProfile(User $user, UserAddress $address): void
    {
        $user->update([
            'address' => $address->line1,
            'city' => $address->city,
        ]);
    }

    /**
     * @return list<string>
     */
    private function rewardPerks(?string $tier): array
    {
        return match ($tier) {
            'Gold' => [
                'Uu tien xu ly don hang',
                'Qua tang dac san theo mua',
                'Giam phi van chuyen',
            ],
            'Silver' => [
                'Tich diem nhanh hon',
                'Nhan uu dai theo mua',
            ],
            default => [
                'Tich diem sau moi don hang',
                'Nhan thong bao uu dai thanh vien',
            ],
        };
    }
}
