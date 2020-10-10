<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Nomination;
use App\Models\Position;
use App\Models\Ranking;
use App\Models\Vote;

class ResultsEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voterNumbers;
    public $position;
    public $votersReceived;
    public $rankersReceived;
    public $nomineesForRanking;
    public $votesCount;
    public $rankscount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->voterNumbers = range(1, \Config::get('fcc.voter_count'));
        $this->position = Position::getDefault();
        $this->votersReceived = Vote::getVotedVoters();
        $this->rankersReceived = Ranking::getRankedVoters();
        $this->nomineesForRanking = Nomination::getNomineesForRanking(null, true);
        $this->votesCount = Vote::count();
        $this->ranksCount = Ranking::count();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['results-channel'];
    }

    public function broadcastAs()
    {
        return 'results-updated';
    }
}
