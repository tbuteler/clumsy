<?php

namespace Clumsy\CMS\Console;

use Clumsy\CMS\Facades\Overseer;
use Clumsy\CMS\Models\Group;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Register a user for access to the Clumsy admin area
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
class RegisterUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clumsy:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a user for access to the Clumsy admin area';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->ask('What is the new user\'s email address?');
        $email = $this->validateEmail($email);
        $name = $this->ask('What is the new user\'s name?');
        $password = $this->secret('What is the new user\'s password?');
        $groupName = str_singular($this->ask('What is the new user\'s level, if any?', 'User'));
        $groupName = Str::lower($groupName) !== 'user' ? $groupName : null;

        $user = Overseer::register([
            'email'    => $email,
            'name'     => $name,
            'password' => $password,
        ]);

        if ($groupName) {
            $group = Group::firstOrCreate([
                'name' => $groupName,
            ]);

            $group->addUser($user);
            return $this->info("Addded user \"{$name}\" as {$groupName}.");
        }

        return $this->info("Addded user \"{$name}\".");
    }

    protected function validateEmail($email)
    {
        $userModel = Overseer::getUserModel();
        $user = new $userModel;

        while (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = $this->ask("\"{$email}\" is not a valid email. What is the new user's email address?");
        }

        while ($user->where('email', $email)->first()) {
            $email = $this->ask("\"{$email}\" is already taken. What is the new user's email address?");
        }

        return $email;
    }
}
