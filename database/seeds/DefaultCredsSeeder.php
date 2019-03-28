<?php

use App\Credential\Credential;
use Illuminate\Database\Seeder;

class DefaultCredsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Create default creds from the env file and populate the credintials table.
     *
     * @return void
     */
    public function run()
    {
        $creds = new Credential();

        $creds->name = 'Default';
        $creds->type = 'password';
        $creds->username = env('DEFAULT_USERNAME');
        $creds->passkey = env('DEFAULT_PASSWORD');

        $creds->save();
    }
}
