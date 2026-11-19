<?php

namespace App\Services;

use App\Models\Portal;
use Illuminate\Support\Facades\DB;

class PortalUserDirectory
{
    /** Store the portal owner profile centrally without copying the login password. */
    public function syncOwner(Portal $portal): void
    {
        DB::table('portal_users')->updateOrInsert(
            ['portal_id' => $portal->id, 'tenant_user_id' => 0],
            [
                'is_portal_owner' => true,
                'username' => $portal->editor_mobile,
                'user_full_name' => $portal->editor_name ?: $portal->portal_name,
                'email' => $portal->editor_email,
                'user_mobile' => $portal->editor_mobile,
                'activated' => true,
                'banned' => false,
                'updated_at' => now(),
                'created_at' => $portal->created_at ?: now(),
            ]
        );
    }

    /** Copy tenant staff profiles into the central directory, excluding passwords and reset tokens. */
    public function syncTenantUsers(Portal $portal): void
    {
        $tenant = DB::connection('tenant');
        if (!$tenant->getSchemaBuilder()->hasTable('users')) {
            return;
        }

        $users = $tenant->table('users')->get([
            'id', 'username', 'user_full_name', 'user_bn_name', 'user_address', 'user_type',
            'email', 'user_mobile', 'user_web', 'user_bio', 'user_designation', 'img_ext',
            'activated', 'banned', 'ban_reason', 'last_ip', 'last_login', 'created',
        ]);
        $syncedIds = [];

        foreach ($users as $user) {
            $tenantUserId = (int) $user->id;
            $syncedIds[] = $tenantUserId;
            $attributes = [
                'is_portal_owner' => false,
                'username' => $user->username,
                'user_full_name' => $user->user_full_name,
                'user_bn_name' => $user->user_bn_name,
                'user_address' => $user->user_address,
                'user_type' => $user->user_type,
                'email' => $user->email,
                'user_mobile' => $user->user_mobile,
                'user_web' => $user->user_web,
                'user_bio' => $user->user_bio,
                'user_designation' => $user->user_designation,
                'img_ext' => $user->img_ext,
                'activated' => (bool) $user->activated,
                'banned' => (bool) $user->banned,
                'ban_reason' => $user->ban_reason,
                'last_ip' => $user->last_ip,
                'last_login' => $user->last_login,
                'tenant_created_at' => $user->created,
                'updated_at' => now(),
            ];
            $exists = DB::table('portal_users')->where('portal_id', $portal->id)
                ->where('tenant_user_id', $tenantUserId)->exists();
            if (!$exists) {
                $attributes['created_at'] = now();
            }

            DB::table('portal_users')->updateOrInsert(
                ['portal_id' => $portal->id, 'tenant_user_id' => $tenantUserId],
                $attributes
            );
        }

        $stale = DB::table('portal_users')->where('portal_id', $portal->id)->where('is_portal_owner', false);
        if ($syncedIds) {
            $stale->whereNotIn('tenant_user_id', $syncedIds);
        }
        $stale->update(['activated' => false, 'updated_at' => now()]);
    }
}
