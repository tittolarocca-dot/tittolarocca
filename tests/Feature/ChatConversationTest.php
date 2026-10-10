<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\ConversationState;
use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatConversationTest extends TestCase
{
    use RefreshDatabase;

    private function setupChat(): array
    {
        $city = City::create(['name' => 'Zürich', 'canton' => 'ZH', 'slug' => 'zuerich']);
        $cat  = Category::create(['name' => 'Escort', 'slug' => 'escort']);

        $adv = User::create([
            'name' => 'Lola', 'email' => 'lola@x.ch', 'password' => bcrypt('x'),
            'role' => 'inserent', 'email_verified_at' => now(),
        ]);
        $profile = Profile::create([
            'user_id' => $adv->id, 'display_name' => 'Lola', 'status' => 'active',
            'verification_status' => 'approved', 'city_id' => $city->id,
            'category_id' => $cat->id, 'subscription_price_chf' => 0,
        ]);

        $mem = User::create([
            'name' => 'Max', 'email' => 'max@x.ch', 'password' => bcrypt('x'),
            'role' => 'member', 'email_verified_at' => now(),
        ]);

        Message::create(['from_user_id' => $mem->id, 'to_user_id' => $adv->id, 'profile_id' => $profile->id, 'body' => 'Hallo']);
        Message::create(['from_user_id' => $adv->id, 'to_user_id' => $mem->id, 'profile_id' => $profile->id, 'body' => 'Hi Max']);

        return [$adv, $mem, $profile];
    }

    public function test_both_message_pages_render(): void
    {
        [$adv, $mem] = $this->setupChat();

        $this->actingAs($mem)->get('/konto/nachrichten')->assertOk();
        $this->actingAs($adv)->get('/inserat/nachrichten')->assertOk();
    }

    public function test_hide_mark_unread_block_report_clear(): void
    {
        [$adv, $mem] = $this->setupChat();

        // Verstecken
        $this->actingAs($mem)->post(route('chat.hide', $adv->id))->assertRedirect();
        $this->assertNotNull(ConversationState::for($mem->id, $adv->id)->hidden_at);

        // Ungelesen
        $this->actingAs($mem)->post(route('chat.unread', $adv->id))->assertRedirect();
        $this->assertNotNull(ConversationState::for($mem->id, $adv->id)->marked_unread_at);

        // Ignorieren = blockieren (+ ChatBlock)
        $this->actingAs($mem)->post(route('chat.block', $adv->id))->assertRedirect();
        $this->assertDatabaseHas('chat_blocks', ['user_id' => $mem->id, 'blocked_user_id' => $adv->id]);

        // Inserent kann blockiertem Mitglied nicht mehr schreiben
        $this->actingAs($adv)->post(route('inserat.messages.reply', $mem->id), ['body' => 'Hey'])
            ->assertSessionHas('error');
        $this->assertDatabaseMissing('messages', ['from_user_id' => $adv->id, 'to_user_id' => $mem->id, 'body_encrypted' => null]);

        // Entsperren
        $this->actingAs($mem)->post(route('chat.unblock', $adv->id))->assertRedirect();
        $this->assertDatabaseMissing('chat_blocks', ['user_id' => $mem->id, 'blocked_user_id' => $adv->id]);

        // Melden → Report
        $this->actingAs($mem)->post(route('chat.report', $adv->id))->assertRedirect();
        $this->assertDatabaseHas('reports', ['reporter_user_id' => $mem->id, 'target_type' => 'user', 'target_id' => $adv->id]);

        // Löschen (clear) → cleared_at gesetzt, Thread danach leer
        $this->actingAs($mem)->post(route('chat.clear', $adv->id))->assertRedirect();
        $this->assertNotNull(ConversationState::for($mem->id, $adv->id)->cleared_at);

        $res = $this->actingAs($mem)->getJson(route('konto.messages.conversation', $adv->id));
        $res->assertOk();
        $this->assertCount(0, $res->json('messages'), 'Nach Löschen sollte der Verlauf für das Mitglied leer sein.');
    }

    public function test_self_action_forbidden(): void
    {
        [, $mem] = $this->setupChat();
        $this->actingAs($mem)->post(route('chat.hide', $mem->id))->assertForbidden();
    }
}
