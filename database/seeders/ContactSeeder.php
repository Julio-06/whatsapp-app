<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::where('email', 'jtcesarin1998@gmail.com')->first();

        $users = User::whereNot('email', 'jtcesarin1998@gmail.com')->get()->take(5);

        foreach($users as $user){
            $user1->contacts()->create([
                'name' => $user->name,
                'contact_id' => $user->id
            ]);
        }
       
    }
}
