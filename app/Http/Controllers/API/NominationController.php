<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\Position;
use App\Models\Ranking;
use App\Models\Vote;

class NominationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Nomination::where('position_id', $request->get('position_id'))
          ->where('year', date('Y'))
          ->get();
    }

    public function getResults() {
        $voterNumbers = range(1, \Config::get('fcc.voter_count'));
        $position = Position::getDefault();
        $votersReceived = Vote::getVotedVoters();
        $rankersReceived = Ranking::getRankedVoters();
        $nomineesForRanking = Nomination::getNomineesForRanking(null,true);
        $votesCount = Vote::count();
        $ranksCount = Ranking::count();

        return compact([
            'voterNumbers',
            'position',
            'votersReceived',
            'rankersReceived',
            'nomineesForRanking',
            'votesCount',
            'ranksCount',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Import nominees from text (CSV or newline-separated)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'nominees_text' => 'required|string',
        ]);

        $positionId = $request->position_id;
        $text = $request->nominees_text;
        $year = date('Y');
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Split by newlines or commas
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Try to parse "Last, First" or "First Last" format
            if (strpos($line, ',') !== false) {
                // Format: "Last, First"
                $parts = array_map('trim', explode(',', $line, 2));
                $lastName = $parts[0];
                $firstName = $parts[1] ?? '';
            } else {
                // Format: "First Last" - split on whitespace (spaces or tabs)
                $parts = preg_split('/\s+/', $line);
                $parts = array_map('trim', $parts);
                $parts = array_filter($parts); // Remove empty elements
                $parts = array_values($parts); // Re-index array

                if (count($parts) >= 2) {
                    $lastName = array_pop($parts);
                    $firstName = implode(' ', $parts);
                } else {
                    $lastName = $parts[0] ?? '';
                    $firstName = '';
                }
            }

            if (empty($lastName)) {
                $errors[] = "Skipped invalid entry: {$line}";
                $skipped++;
                continue;
            }

            // Check if nominee already exists
            $exists = Nomination::where('position_id', $positionId)
                ->where('year', $year)
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Create nominee
            Nomination::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position_id' => $positionId,
                'year' => $year,
            ]);

            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Imported {$imported} nominee(s). Skipped {$skipped} duplicate(s)."
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
