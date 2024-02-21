<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Carbon\Carbon;

class LoanService
{
    /**
     * Create a Loan
     *
     * @param  User  $user
     * @param  int  $amount
     * @param  string  $currencyCode
     * @param  int  $terms
     * @param  string  $processedAt
     *
     * @return Loan
     */
    public function createLoan(User $user, int $amount, string $currencyCode, int $terms, string $processedAt): Loan
    {
        $loan = Loan::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'terms' => $terms,
            'processed_at' => $processedAt,
            'outstanding_amount' => $amount,
            'status' => Loan::STATUS_DUE,
        ]);

        $amountPerInstallment = round($amount / $terms);

        for ($i = 0; $i < $terms; $i++) {
            $dueDate = Carbon::parse($processedAt)->addMonths($i + 1)->toDateString();

            ScheduledRepayment::create([
                'loan_id'=> $loan->id,
                'amount' => $amountPerInstallment,
                'outstanding_amount' => $amountPerInstallment,
                'currency_code' => $currencyCode,
                'due_date' => $dueDate,
                'status' => ScheduledRepayment::STATUS_DUE,
            ]);
        }

        return $loan;
    }


    /**
     * Repay Scheduled Repayments for a Loan
     *
     * @param  Loan  $loan
     * @param  int  $amount
     * @param  string  $currencyCode
     * @param  string  $receivedAt
     *
     * @return ReceivedRepayment
     */
    public function repayLoan(Loan $loan, int $amount, string $currencyCode, string $receivedAt): ReceivedRepayment
    {
        $remainingAmount = $loan->outstanding_amount - $amount;

        // Update the corresponding scheduled repayment
        $scheduledRepayment = $loan->scheduledRepayments()->where('status', ScheduledRepayment::STATUS_DUE)->first();
    
        if ($scheduledRepayment) {
            $scheduledRepayment->update([
                'outstanding_amount' => max(0, $scheduledRepayment->outstanding_amount - $amount),
                'status' => ($remainingAmount <= 0) ? ScheduledRepayment::STATUS_REPAID : ScheduledRepayment::STATUS_DUE,
            ]);
        }
    
        // Update the loan details
        $loan->update([
            'outstanding_amount' => $remainingAmount,
            'status' => ($remainingAmount <= 0) ? Loan::STATUS_REPAID : Loan::STATUS_DUE,
        ]);
    
        // Store the received repayment
        $receivedRepayment = ReceivedRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'received_at' => $receivedAt,
        ]);
    
        return $receivedRepayment;
       }
    
}
