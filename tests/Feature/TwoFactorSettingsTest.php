<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TwoFactorSettingsTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'TFA Settings',
            'email' => 'tfa-settings-'.uniqid().'@example.test',
            'password' => bcrypt('secret-password-123'),
            'storage_limit_mb' => 100,
        ]);
        $this->user->markEmailAsVerified();
    }

    protected function tearDown(): void
    {
        $this->user->forceDelete();
        parent::tearDown();
    }

    public function test_settings_page_shows_enable_button_when_2fa_disabled(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.settings'))
            ->assertOk()
            ->assertSee('Enable Two-Factor Authentication')
            ->assertDontSee('Disable 2FA');
    }

    public function test_user_can_enable_and_disable_2fa_after_confirming_password(): void
    {
        $this->actingAs($this->user);

        // Confirm the password so the password.confirm middleware lets us through
        // (mirrors what the settings-page modal does before submitting).
        $this->postJson(route('password.confirm'), ['password' => 'secret-password-123'])
            ->assertStatus(201);

        $this->post(route('two-factor.enable'))->assertRedirect();
        $this->assertTrue($this->user->fresh()->hasEnabledTwoFactorAuthentication());

        // Settings page now renders the QR + recovery codes + disable control.
        $this->get(route('account.settings'))
            ->assertOk()
            ->assertSee('is <strong class="mx-1">enabled</strong>', false)
            ->assertSee('Disable 2FA');

        $this->delete(route('two-factor.disable'))->assertRedirect();
        $this->assertFalse($this->user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_enabling_2fa_requires_confirmed_password(): void
    {
        // Without confirming the password first, the enable endpoint is gated.
        $this->actingAs($this->user)
            ->post(route('two-factor.enable'))
            ->assertRedirect(route('password.confirm'));

        $this->assertFalse($this->user->fresh()->hasEnabledTwoFactorAuthentication());
    }
}
