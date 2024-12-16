<?php

namespace App\Http\Controllers;


use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\Models\ManagementSDM\Candidate;
use App\Models\ManagementSDM\Recruitment;

class CandidateController extends Controller
{
    public function showApplicationForm(Recruitment $record, $recruitment)
    {
        $recruitment = $record->findOrFail($recruitment); // Use findOrFail instead of find
        return view('candidates.apply', compact('recruitment'));
    }

    public function submitApplication(Request $request, Candidate $candidate, Recruitment $recruitment)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:candidates,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'national_id_number' => 'nullable|string|max:255|unique:candidates,national_id_number',
            'position_applied' => 'required|string|max:255',
            'resume' => 'required|file|mimes:pdf|max:5120',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('resume')) {
            $validatedData['resume'] = $request->file('resume')->store('candidate-resumes', 'public');
        }

        try {
            $candidate = Candidate::create([
                'company_id' => $recruitment->company_id,
                'branch_id' => $recruitment->branch_id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'gender' => $validatedData['gender'],
                'national_id_number' => $validatedData['national_id_number'],
                'position_applied' => $validatedData['position_applied'],
                'resume' => $validatedData['resume'],
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'postal_code' => $validatedData['postal_code'],
                'country' => $validatedData['country'],
            ]);

            return view('candidates.thank-you', [
                'message' => 'Thank you for submitting your application!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating candidate: ' . $e->getMessage());
            return view('candidates.thank-you', [
                'message' => 'Error submitting your application. Please try again later.',
            ]);
        }
    }

    public function generatePoster(Recruitment $recruitment)
    {
        // For PDF generation
        if (request()->format === 'pdf') {
            $pdf = app('dompdf.wrapper')->loadView('recruitments.poster', compact('recruitment'));
            return $pdf->download("job-posting-{$recruitment->id}.pdf");
        }
        return view('recruitments.poster', compact('recruitment'));
    }
}
