<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class AdminLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_failed_attempts_per_ip(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $payload = [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ];

        foreach (range(1, 5) as $attempt) {
            $this->postJson(route('login'), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        }

        $this->postJson(route('login'), $payload)
            ->assertTooManyRequests()
            ->assertJsonPath('message', 'محاولات دخول كثيرة. انتظر دقيقة ثم حاول مجدداً.');
    }

    public function test_successful_login_clears_the_rate_limiter(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        foreach (range(1, 4) as $attempt) {
            $this->postJson(route('login'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->assertSame(0, RateLimiter::attempts(md5('admin-login127.0.0.1')));

        $this->postJson(route('logout'))->assertOk();

        $this->postJson(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }
}
