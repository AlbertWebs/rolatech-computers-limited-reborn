<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create-user {name} {email} {--role=manager : owner, manager, catalog, operations, or content}';

    protected $description = 'Create an administrator account from a secure interactive prompt';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Run this command interactively so the password can be entered without echoing it.');

            return self::FAILURE;
        }

        $email = mb_strtolower((string) $this->argument('email'));
        $role = (string) $this->option('role');
        if (! in_array($role, ['owner', 'manager', 'catalog', 'operations', 'content'], true)) {
            $this->error('Choose one of these administrator roles: owner, manager, catalog, operations, content.');

            return self::FAILURE;
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::query()->where('email', $email)->exists()) {
            $this->error('Use a valid email address that does not already have an account.');

            return self::FAILURE;
        }

        $password = $this->secret('Enter an administrator password (minimum 12 characters)');
        $confirmation = $this->secret('Confirm the administrator password');

        if (! is_string($password) || strlen($password) < 12 || ! hash_equals($password, (string) $confirmation)) {
            $this->error('The password must contain at least 12 characters and both entries must match.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => (string) $this->argument('name'),
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
            'is_active' => true,
            'admin_role' => $role,
        ]);

        $this->info("Administrator account created with the {$role} role. The password was not displayed or stored in command arguments.");

        return self::SUCCESS;
    }
}
