<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_marketing_content_can_be_customized_from_store_settings(): void
    {
        StoreSetting::set('announcement_bar_text', 'شحن مجاني مخصص', 'string', 'homepage');
        StoreSetting::set('home_features', [
            ['icon' => '⭐', 'title' => 'ميزة مخصصة', 'subtitle' => 'نص مخصص'],
        ], 'json', 'homepage');
        StoreSetting::set('promo_banner_badge', 'شارة مخصصة', 'string', 'homepage');
        StoreSetting::set('promo_banner_title', 'عنوان عرض مخصص', 'string', 'homepage');
        StoreSetting::set('promo_banner_subtitle', 'وصف عرض مخصص', 'string', 'homepage');

        $this->get('/')
            ->assertOk()
            ->assertSee('شحن مجاني مخصص')
            ->assertSee('ميزة مخصصة')
            ->assertSee('نص مخصص')
            ->assertSee('شارة مخصصة')
            ->assertSee('عنوان عرض مخصص')
            ->assertSee('وصف عرض مخصص');
    }

    public function test_contact_page_uses_store_settings_contact_details(): void
    {
        StoreSetting::set('contact_phone', '0555555555', 'string', 'contact');
        StoreSetting::set('contact_email', 'support@example.com', 'string', 'contact');
        StoreSetting::set('contact_working_hours', 'كل يوم من 10 صباحاً حتى 8 مساءً', 'string', 'contact');
        StoreSetting::set('contact_facebook_url', 'https://facebook.com/store', 'string', 'contact');
        StoreSetting::set('contact_instagram_url', 'https://instagram.com/store', 'string', 'contact');
        StoreSetting::set('contact_twitter_url', 'https://x.com/store', 'string', 'contact');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('0555555555')
            ->assertSee('support@example.com')
            ->assertSee('كل يوم من 10 صباحاً حتى 8 مساءً')
            ->assertSee('https://facebook.com/store')
            ->assertSee('https://instagram.com/store')
            ->assertSee('https://x.com/store');
    }
}
