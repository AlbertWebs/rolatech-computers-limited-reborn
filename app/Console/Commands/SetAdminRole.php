<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetAdminRole extends Command
{
    protected $signature = 'admin:set-role {email} {role}';

    protected $description = 'Assign an administrator role from the server console';

    public function handle(): int
    {
        $role = (string) $this->argument('role');
        if (! in_array($role, ['owner', 'manager', 'catalog', 'operations', 'content'], true)) {
            $this->error('Choose one of: owner, manager, catalog, operations, content.');

            return self::FAILURE;
        }
        $user = User::query()->where('email', mb_strtolower((string) $this->argument('email')))->first();
        if (! $user || ! $user->is_admin) {
            $this->error('No administrator account was found for that email.');

            return self::FAILURE;
        }
        $user->update(['admin_role' => $role]);

        $this->info("Administrator role set to {$role}.");

        return self::SUCCESS;
    }
}
