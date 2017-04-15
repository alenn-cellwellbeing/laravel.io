<?php

namespace Tests\Features;

use Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\BrowserKitTestCase;

class SettingsTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /** @test */
    function requires_login()
    {
        $this->visit('/settings')
            ->seePageIs('/login');
    }

    /** @test */
    function users_can_update_their_profile()
    {
        $this->login();

        $this->visit('/settings')
            ->submitForm('Save', [
                'name' => 'Freek Murze',
                'email' => 'freek@example.com',
                'username' => 'freekmurze',
            ])
            ->seePageIs('/settings')
            ->see('Freek Murze')
            ->see('freekmurze')
            ->see('Settings successfully saved!');
    }

    /** @test */
    function users_cannot_choose_duplicate_usernames_or_email_addresses()
    {
        $this->createUser(['email' => 'freek@example.com', 'username' => 'freekmurze']);

        $this->login();

        $this->visit('/settings')
            ->submitForm('Save', [
                'name' => 'Freek Murze',
                'email' => 'freek@example.com',
                'username' => 'freekmurze',
            ])
            ->seePageIs('/settings')
            ->see('Something went wrong. Please review the fields below.')
            ->see('The email has already been taken.')
            ->see('The username has already been taken.');
    }

    /** @test */
    function users_can_update_their_password()
    {
        $this->login();

        $this->visit('/settings/password')
            ->submitForm('Save', [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->seePageIs('/settings/password')
            ->see('Password successfully changed!');

        $this->assertPasswordWasHashedAndSaved();
    }

    private function assertPasswordWasHashedAndSaved()
    {
        return $this->assertTrue($this->app['hash']->check('newpassword', Auth::user()->getAuthPassword()));
    }
}
