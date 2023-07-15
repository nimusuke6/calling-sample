<?php

namespace Tests\Feature\Controllers;

use App\Enums\PhoneCallStatus;
use App\Models\PhoneCall;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PhoneCallControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_電話をかけることができる(): void
    {
        // given
        CarbonImmutable::setTestNow($now = CarbonImmutable::now());

        /** @var User $me */
        $me = User::factory()->create();
        $this->actingAs($me);

        /** @var User $receiver */
        $receiver = User::factory()->create();

        // when
        $actual = $this->postJson('/api/phone_calls', [
            'user_id' => $receiver->id
        ]);

        // then
        $actual->assertCreated();

        /** @var PhoneCall $latest */
        $latest = PhoneCall::query()->latest()->first();
        $actual->assertJsonFragment([
            'phone_call_id' => $latest->id
        ]);

        $this->assertDatabaseHas(PhoneCall::class, [
            'caller_user_id' => $me->id,
            'receiver_user_id' => $receiver->id,
            'status' => PhoneCallStatus::WaitingReceiver->value,
            'called_at' => $now
        ]);
    }

    public function test_電話をキャンセルすることができる(): void
    {
        // given
        CarbonImmutable::setTestNow($now = CarbonImmutable::now());

        /** @var PhoneCall $phoneCall */
        $phoneCall = PhoneCall::factory()->create();

        // when
        $actual = $this->postJson("/api/phone_calls/{$phoneCall->id}/cancel");

        // then
        $actual->assertNoContent();

        $this->assertDatabaseHas(PhoneCall::class, [
            'id' => $phoneCall->id,
            'status' => PhoneCallStatus::Canceled->value,
            'finished_at' => $now
        ]);
    }

    public function test_電話を受けることができる(): void
    {
        // given
        CarbonImmutable::setTestNow($now = CarbonImmutable::now());

        /** @var PhoneCall $phoneCall */
        $phoneCall = PhoneCall::factory()->create();

        // when
        $actual = $this->postJson("/api/phone_calls/{$phoneCall->id}/receive");

        // then
        $actual->assertNoContent();

        $this->assertDatabaseHas(PhoneCall::class, [
            'id' => $phoneCall->id,
            'status' => PhoneCallStatus::TalkStarted->value,
            'talk_started_at' => $now
        ]);
    }

    public function test_通話終了することができる(): void
    {
        // given
        CarbonImmutable::setTestNow($now = CarbonImmutable::now());

        /** @var PhoneCall $phoneCall */
        $phoneCall = PhoneCall::factory()->create([
            'talk_started_at' => $now->addMinutes(11)
        ]);

        // when
        $actual = $this->postJson("/api/phone_calls/{$phoneCall->id}/finish");

        // then
        $actual->assertNoContent();

        $this->assertDatabaseHas(PhoneCall::class, [
            'id' => $phoneCall->id,
            'status' => PhoneCallStatus::Finished->value,
            'finished_at' => $now,
            'call_charge' => 100
        ]);
    }
}
