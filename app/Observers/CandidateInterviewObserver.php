<?php

namespace App\Observers;

use App\Models\ManagementSDM\Applications;
use App\Models\ManagementSDM\Candidate;
use App\Models\ManagementSDM\CandidateInterview;


class CandidateInterviewObserver
{
    /**
     * Handle the CandidateInterview "created" event.
     */
    public function created(CandidateInterview $candidateInterview): void
    {
        //
    }

    /**
     * Handle the CandidateInterview "updated" event.
     */
    public function updated(CandidateInterview $candidateInterview): void
    {
        if ($candidateInterview->isDirty('result')) {
            $candidate = Candidate::find($candidateInterview->candidate_id);
            $applicationStatus = Applications::find($candidateInterview->candidate_id);

            if ($candidateInterview->result === 'failed') {
                $candidate->update(['status' => 'rejected']);
                $applicationStatus->update(['status' => 'rejected']);
            }

            if ($candidateInterview->result === 'passed') {
                $candidate->update(['status' => 'offered']);
                $applicationStatus->update(['status' => 'offered']);
            }
        }
    }

    /**
     * Handle the CandidateInterview "deleted" event.
     */
    public function deleted(CandidateInterview $candidateInterview): void
    {
        //
    }

    /**
     * Handle the CandidateInterview "restored" event.
     */
    public function restored(CandidateInterview $candidateInterview): void
    {
        //
    }

    /**
     * Handle the CandidateInterview "force deleted" event.
     */
    public function forceDeleted(CandidateInterview $candidateInterview): void
    {
        //
    }
}
