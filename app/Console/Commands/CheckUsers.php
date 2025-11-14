<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check users in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::with('roles')->get();

        $this->info('Toplam kullanıcı sayısı: '.$users->count());
        $this->info('Kullanıcılar:');

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $this->line('- '.$user->name.' ('.$user->email.') - Roller: '.($roles ?: 'Rol yok'));
        }
    }
}
