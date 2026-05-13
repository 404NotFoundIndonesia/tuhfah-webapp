<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // ── Display ───────────────────────────────────────────────────────────────

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account.profile.edit'))
            ->assertOk();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '08123456789',
                'gender' => 'male',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('account.profile.edit'));

        $this->assertSame('Updated Name', $user->fresh()->name);
    }

    public function test_email_verification_cleared_when_email_changed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => $user->name,
                'email' => 'newemail@example.com',
                'phone' => $user->phone,
                'gender' => 'male',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_unchanged_when_email_not_changed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => 'New Name',
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_profile_update_with_image_stores_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => 'male',
                'image' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->image);
        Storage::disk('local')->assertExists('public/'.$user->image);
    }

    public function test_profile_update_replaces_old_image(): void
    {
        Storage::fake('local');

        $oldFilename = 'old_avatar.jpg';
        Storage::disk('local')->put('public/'.$oldFilename, 'content');
        $user = User::factory()->create(['image' => $oldFilename]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => 'male',
                'image' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('local')->assertMissing('public/'.$oldFilename);
        Storage::disk('local')->assertExists('public/'.$user->fresh()->image);
    }

    // ── Locale ────────────────────────────────────────────────────────────────

    public function test_user_can_change_locale_to_english(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->get(route('account.locale', ['locale' => 'en']))
            ->assertRedirect();

        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_user_can_change_locale_to_indonesian(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->get(route('account.locale', ['locale' => 'id']))
            ->assertRedirect();

        $this->assertSame('id', $user->fresh()->locale);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->get(route('account.locale', ['locale' => 'fr']))
            ->assertRedirect();

        // Locale must not change
        $this->assertSame('id', $user->fresh()->locale);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('account.profile.destroy'), ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_account_deletion_removes_profile_image(): void
    {
        Storage::fake('local');

        $filename = 'avatar.jpg';
        Storage::disk('local')->put('public/'.$filename, 'content');
        $user = User::factory()->create(['image' => $filename]);

        $this->actingAs($user)
            ->delete(route('account.profile.destroy'), ['password' => 'password']);

        Storage::disk('local')->assertMissing('public/'.$filename);
    }

    public function test_correct_password_required_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.profile.edit'))
            ->delete(route('account.profile.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('account.profile.edit'));

        $this->assertNotNull($user->fresh());
    }
}
