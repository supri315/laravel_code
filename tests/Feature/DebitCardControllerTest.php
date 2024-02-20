<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DebitCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCards()
    {

        DebitCard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->json('GET', 'api/debit-cards');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                'id',
                'number',
                'type',
                'expiration_date',
                'is_active',
            ],
        ]);

    }

    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        DebitCard::factory()->create();

        $response = $this->json('GET', 'api/debit-cards');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                'id',
                'number',
                'type',
                'expiration_date',
                'is_active',
            ],
        ]);    
    }

    public function testCustomerCanCreateADebitCard()
    {
  
        $debitCard = DebitCard::factory()->create();

        $data = [
            'type' => $debitCard['type']
        ];
        
        $response = $this->json('POST', 'api/debit-cards', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'number',
                'type',
                'expiration_date',
                'is_active',               
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        $debitCard = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->json('GET', "api/debit-cards/{$debitCard->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'number',
                'type',
                'expiration_date',
                'is_active',
        ]);
    
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        $debitCard = DebitCard::factory()->create();

        $response = $this->json('GET', "api/debit-cards/{$debitCard->id}");
        
        $response->assertStatus(403);
        
    }

    public function testCustomerCanActivateADebitCard()
    {

        $debitCard = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'is_active' => true,
        ];
    
        $response = $this->json('PUT', "api/debit-cards/{$debitCard->id}", $data);
    
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'id',
                    'number',
                    'type',
                    'expiration_date',
                    'is_active',
        ]);

    }

    public function testCustomerCanDeactivateADebitCard()
    {
        $debitCard = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'is_active' => false,
        ];
    
        $response = $this->json('PUT', "api/debit-cards/{$debitCard->id}", $data);
    
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'id',
                    'number',
                    'type',
                    'expiration_date',
                    'is_active',
        ]);
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        $debitCard = DebitCard::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'is_active' => $debitCard->disabled_at,
        ];
    
        $response = $this->json('PUT', "api/debit-cards/{$debitCard->id}", $data);
    
        $response->assertStatus(422);
    }

    public function testCustomerCanDeleteADebitCard()
    {
        $debitCard = DebitCard::factory()->create(['user_id' => $this->user->id]);
    
        $response = $this->json('DELETE', "api/debit-cards/{$debitCard->id}");
    
        $response->assertStatus(204);    
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        $debitCard = DebitCard::factory()->create();
    
        $response = $this->json('DELETE', "api/debit-cards/{$debitCard->id}");
    
        $response->assertStatus(403);    
    }

    // Extra bonus for extra tests :)
}
