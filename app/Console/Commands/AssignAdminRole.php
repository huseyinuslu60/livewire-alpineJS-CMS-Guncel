<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign admin role to user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = \App\Models\User::where('email', $email)->first();

        if (! $user) {
            $this->error('Kullanıcı bulunamadı: '.$email);

            return;
        }

        $user->assignRole('admin');
        $this->info('Admin rolü atandı: '.$user->name.' ('.$user->email.')');
    }
}
