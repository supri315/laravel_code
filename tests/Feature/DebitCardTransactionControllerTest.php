<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\User;
use App\Models\DebitCardTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DebitCard $debitCard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id
        ]);
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCardTransactions()
    {
        
        DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id
        ]);
  
        $response = $this->json('GET', 'api/debit-card-transactions', ['debit_card_id' => $this->debitCard->id]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                'amount',
                'currency_code'
            ],
        ]);
    
    }

    public function testCustomerCannotSeeAListOfDebitCardTransactionsOfOtherCustomerDebitCard()
    {
        
        DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id
        ]);
  
        $response = $this->json('GET', 'api/debit-card-transactions', ['debit_card_id' => !$this->debitCard->id]);

        $response->assertStatus(403);
           
    }

    public function testCustomerCanCreateADebitCardTransaction()
    {
        $debitCardTransaction = DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id
        ]);

        $data = [
            'amount' => $debitCardTransaction['amount'],
            'currency_code' => $debitCardTransaction['currency_code'],
            'debit_card_id' => $debitCardTransaction['debit_card_id'],
        ];
  
        $response = $this->json('POST', 'api/debit-card-transactions',$data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'amount',
                'currency_code' 
        ]);
    }

    public function testCustomerCannotCreateADebitCardTransactionToOtherCustomerDebitCard()
    {
        $debitCardTransaction = DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id
        ]);

        $data = [
            'amount' => $debitCardTransaction['amount'],
            'currency_code' => $debitCardTransaction['currency_code'],
            'debit_card_id' => 476,
        ];
  
        $response = $this->json('POST', 'api/debit-card-transactions',$data);

        $response->assertStatus(403);
   
    }

    public function testCustomerCanSeeADebitCardTransaction()
    {

        $debitCardTransaction = DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id
        ]);
        
        $response = $this->json('GET', "api/debit-card-transactions/{$debitCardTransaction->debitCard->number}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'amount',
                'currency_code' 
        ]);
        
    }

    public function testCustomerCannotSeeADebitCardTransactionAttachedToOtherCustomerDebitCard()
    {
        $debitCardTransaction = DebitCardTransaction::factory()->create();
        
    
        $response = $this->json('GET', "api/debit-card-transactions/{$debitCardTransaction->debitCard->number}");
        
        $response->assertStatus(403);
    }

    // Extra bonus for extra tests :)
}
