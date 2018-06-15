<?php

use App\User;
use App\Verification;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    private const USER_LIST = [
        [ 'email' => 'deniz.ozsen@gmail.com',   'name' => 'Deniz Ozsen', 'password' => '12345', 'role' => 'admin' ],
        [ 'email' => 'deniz.ozsen+2@gmail.com', 'name' => 'Deniz T2',    'password' => '22222' ],
        [ 'email' => 'deniz.ozsen+3@gmail.com', 'name' => 'Tester 3',    'password' => '33333' ],
    ];

    public function run()
    {
        foreach (self::USER_LIST as $userData) {
            $user = new User($userData);
            $user->save();
            $verification = new Verification([
                'user' => $user->id,
                'done' => 1,
                'code' => '123456',
            ]);
            $verification->save();
        }
    }
}
