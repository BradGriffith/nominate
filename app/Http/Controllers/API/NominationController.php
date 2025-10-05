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
          ->orderBy('last_name')
          ->orderBy('first_name')
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
     * Preview import to get counts before importing
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'nominees_text' => 'required|string',
        ]);

        $positionId = $request->position_id;
        $text = $request->nominees_text;
        $year = date('Y');
        $toImport = 0;
        $duplicates = 0;
        $seenInList = []; // Track nominees already seen in this import

        // Split by newlines
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Try to parse "Last, First" or "First Last" format
            if (strpos($line, ',') !== false) {
                $parts = array_map('trim', explode(',', $line, 2));
                $lastName = trim($parts[0]);
                $firstName = trim($parts[1] ?? '');
            } else {
                $parts = preg_split('/\s+/', $line);
                $parts = array_map('trim', $parts);
                $parts = array_filter($parts);
                $parts = array_values($parts);

                if (count($parts) >= 2) {
                    $lastName = trim(array_pop($parts));
                    $firstName = trim(implode(' ', $parts));
                } else {
                    $lastName = trim($parts[0] ?? '');
                    $firstName = '';
                }
            }

            if (empty($lastName)) {
                continue;
            }

            // Create normalized key for duplicate checking
            $normalizedKey = strtolower($lastName) . '|' . strtolower($firstName);

            // Check if already seen in this import list
            if (isset($seenInList[$normalizedKey])) {
                $duplicates++;
                continue;
            }

            // Check if nominee already exists in database (case-insensitive)
            $exists = Nomination::where('position_id', $positionId)
                ->where('year', $year)
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower($firstName)])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [strtolower($lastName)])
                ->exists();

            if ($exists) {
                $duplicates++;
            } else {
                $toImport++;
                $seenInList[$normalizedKey] = true; // Mark as seen
            }
        }

        return response()->json([
            'to_import' => $toImport,
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * Add nominees from text (CSV or newline-separated)
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
        $added = 0;
        $skipped = 0;
        $errors = [];
        $seenInList = []; // Track nominees already seen in this import

        // Split by newlines or commas
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Try to parse "Last, First" or "First Last" format
            if (strpos($line, ',') !== false) {
                // Format: "Last, First"
                $parts = array_map('trim', explode(',', $line, 2));
                $lastName = trim($parts[0]);
                $firstName = trim($parts[1] ?? '');
            } else {
                // Format: "First Last" - split on whitespace (spaces or tabs)
                $parts = preg_split('/\s+/', $line);
                $parts = array_map('trim', $parts);
                $parts = array_filter($parts); // Remove empty elements
                $parts = array_values($parts); // Re-index array

                if (count($parts) >= 2) {
                    $lastName = trim(array_pop($parts));
                    $firstName = trim(implode(' ', $parts));
                } else {
                    $lastName = trim($parts[0] ?? '');
                    $firstName = '';
                }
            }

            if (empty($lastName)) {
                $errors[] = "Skipped invalid entry: {$line}";
                $skipped++;
                continue;
            }

            // Create normalized key for duplicate checking
            $normalizedKey = strtolower($lastName) . '|' . strtolower($firstName);

            // Check if already seen in this import list
            if (isset($seenInList[$normalizedKey])) {
                $skipped++;
                continue;
            }

            // Check if nominee already exists in database (case-insensitive)
            $exists = Nomination::where('position_id', $positionId)
                ->where('year', $year)
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower($firstName)])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [strtolower($lastName)])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Mark as seen in this import
            $seenInList[$normalizedKey] = true;

            // Create nominee (store trimmed values)
            Nomination::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position_id' => $positionId,
                'year' => $year,
            ]);

            $added++;
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Added {$added} nominee(s). Skipped {$skipped} duplicate(s)."
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
     * Get all nominees for a specific position
     *
     * @param  int  $positionId
     * @return \Illuminate\Http\Response
     */
    public function getByPosition($positionId)
    {
        return Nomination::where('position_id', $positionId)
            ->where('year', date('Y'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Check if nominee has any votes or ranks
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function hasVotesOrRanks($id)
    {
        $hasVotes = Vote::where('nomination_id', $id)->exists();
        $hasRanks = Ranking::where('nomination_id', $id)->exists();

        return response()->json([
            'has_votes_or_ranks' => $hasVotes || $hasRanks
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $nomination = Nomination::findOrFail($id);

        // Delete associated votes and ranks
        Vote::where('nomination_id', $id)->delete();
        Ranking::where('nomination_id', $id)->delete();

        // Delete the nomination
        $nomination->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nominee removed successfully'
        ]);
    }
}
