<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('admin:create {email} {password} {name=Admin}', function (string $email, string $password, string $name) {
    Admin::updateOrCreate(
        ['email' => $email],
        ['name' => $name, 'password' => Hash::make($password)]
    );
    $this->info("Admin ready: {$email}");
})->purpose('Create (or reset the password of) an admin panel user');
