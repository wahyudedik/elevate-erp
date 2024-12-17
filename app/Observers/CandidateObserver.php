<?php

namespace App\Observers;

use App\Models\ManagementSDM\Candidate;
use App\Models\ManagementSDM\CandidateInterview;

class CandidateObserver
{
    /**
     * Handle the Candidate "created" event.
     */
    public function created(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "updated" event.
     */
    public function updated(Candidate $candidate): void
    {
        $originalStatus = $candidate->getOriginal('status');
        $newStatus = $candidate->status;

        // If status changed to interviewing, create new interview record
        if ($originalStatus !== 'interviewing' && $newStatus === 'interviewing') {
            CandidateInterview::create([
                'company_id' => $candidate->company_id,
                'branch_id' => $candidate->branch_id,
                'candidate_id' => $candidate->id,
                'interview_date' => now(),
                'interview_type' => 'in_person',
                'result' => 'pending'
            ]);
        }
    }

    /**
     * Handle the Candidate "deleted" event.
     */
    public function deleted(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "restored" event.
     */
    public function restored(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "force deleted" event.
     */
    public function forceDeleted(Candidate $candidate): void
    {
        //
    }
}
