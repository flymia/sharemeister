<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApiKeyDownloadTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Download Test',
            'email' => 'download-test-'.uniqid().'@example.test',
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

    public function test_freshly_generated_key_survives_render_and_is_embedded_in_downloads(): void
    {
        $this->actingAs($this->user);

        // Generate a key - plaintext is flashed to the session.
        $this->post(route('account.settings.generateapikey'))->assertRedirect();
        $token = session('apikey');
        $this->assertNotEmpty($token);

        // Rendering the settings page must NOT consume the key (index keeps it).
        $this->get(route('account.settings'))->assertOk();

        // The ShareX config download embeds the real key, not the placeholder.
        $sxcu = $this->get(route('account.settings.sxcu'));
        $sxcu->assertOk();
        $this->assertStringContainsString('Bearer '.$token, $sxcu->streamedContent());
        $this->assertStringNotContainsString('DEIN_API_KEY_HIER', $sxcu->streamedContent());

        // The bash script embeds it too (still available after the previous download).
        $bash = $this->get(route('account.settings.bash'));
        $bash->assertOk();
        $this->assertStringContainsString('API_KEY="'.$token.'"', $bash->streamedContent());
        $this->assertStringNotContainsString('YOUR_API_KEY_HERE', $bash->streamedContent());
    }

    public function test_download_without_fresh_key_redirects_with_error(): void
    {
        $this->actingAs($this->user);

        // A key exists in the DB, but its plaintext is not in the session.
        $this->user->createToken('sharex-api-key');

        $this->get(route('account.settings.sxcu'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
