<template>
    <div>
        <div class="p-3 sm:px-10 bg-white border-t border-gray-200 fixed bottom-0 left-0 w-full italic bg-yellow-200 z-50">
          <p v-if="position.status == 'vote'">Voting is in progress. When everyone is done voting, click "Start Ranking &raquo;" above.</p>
          <p v-if="position.status == 'rank'">Ranking is in progress. When everyone is done ranking, click "Stop Ranking and Show Final Results &raquo;" above.</p>
          <p v-if="position.status == 'results'">Results are complete. To start voting on a different position, click the name of the position above.</p>
        </div>
        <div class="p-6 sm:px-20 bg-white rounded-t-lg">
            <div class="text-gray-500">
                Here you will find the current status of the nominating process.
            </div>

            <p>Current Position: {{ position.name }}</p>
            <p>Current Status: {{ position.status }}</p>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 border-t border-gray-200 shadow-xl mb-10 rounded-b-lg" v-if="$page.user">
            <div class="text-xl">Admin Controls</div>
            <div class="text-l inline clear">Change Status: </div>
            <div class="inline-flex" v-if="position.status == 'vote'">
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r" @click="updatePositionStatus('rank')">
                    Start Ranking &raquo;
                </button>
            </div>
            <div class="inline-flex" v-if="position.status == 'rank'">
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l" @click="updatePositionStatus('vote')">
                    &laquo; Re-open Voting
                </button>
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r  border-l border-gray-200" @click="updatePositionStatus('results')">
                    Stop Ranking and Show Final Results &raquo;
                </button>
            </div>
            <div class="inline-flex" v-if="position.status == 'results'">
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l" @click="updatePositionStatus('vote')">
                    &laquo; Re-open Voting
                </button>
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r  border-l border-gray-200" @click="updatePositionStatus('rank')">
                    &laquo; Re-open Ranking
                </button>
            </div>
            <div class="p-1"></div>
            <div class="text-l inline">Change Position: </div>
            <div class="inline-flex">
                <button :class="['py-2 px-4 mx-2',{'current text-gray-400': pos.id == position.id, 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold': pos.id != position.id}]" @click="updateDefaultPosition(pos.id)" v-for="pos in positions">
                    {{ pos.name }}
                </button>
            </div>
            <div class="p-1"></div>
            <div class="text-l inline">Change Allowed Votes/Ranks Per Person: </div>
            <div class="inline-flex">
		<select v-model="position.num_to_select">
		    <option v-for="i in 20">{{ i }}</option>
		</select>
		<button class="py-2 px-4 mx-2 bg-orange-400 hover:bg-orange-600 text-gray-200 font-bold" @click="updatePositionVoteCount">
		    Change {{ position.name }} Votes/Ranks Allowed
		</button>
            </div>
            <div v-if="votersReceived.length">
		    <div class="p-1"></div>
		    <div class="text-l inline">Clear Votes: </div>
		    <div class="inline-flex">
			<select v-model="clearVoterId">
			    <option value="all">All</option>
			    <option v-for="voter in votersReceived">{{ voter }}</option>
			</select>
			<button class="py-2 px-4 mx-2 bg-red-700 hover:bg-red-400 text-gray-200 font-bold" @click="clearVotes">
			    Clear {{ position.name }} Votes
			</button>
		    </div>
	    </div>
            <div v-if="rankersReceived.length">
		    <div class="p-1"></div>
		    <div class="text-l inline">Clear Ranks: </div>
		    <div class="inline-flex">
			<select v-model="clearRankerId">
			    <option value="all">All</option>
			    <option v-for="ranker in rankersReceived">{{ ranker }}</option>
			</select>
			<button class="py-2 px-4 mx-2 bg-red-700 hover:bg-red-400 text-gray-200 font-bold" @click="clearRanks">
			    Clear {{ position.name }} Ranks
			</button>
		    </div>
	    </div>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg">
            <div class="text-xl">Voting</div>
            <div v-if="position.status.length">
                <ul>
                    <li>Votes received from: {{ votersReceived.length }} / {{ voterNumbers.length }}</li>
                    <li>Missing votes: {{ votersMissing.join(' &nbsp; ') }}</li>
                </ul>
                <ul class="voter-check">
                    <li v-for="voter in voterNumbers" :class="{ voted: voted(voter) }">{{ voter }}</li>
                </ul>
                <p class="font-bold pt-6">Nominees for ranking:</p>
                <p class="font-italic pb-2 italic" v-if="nomineesForRanking.length > position.num_to_select">There was a tie for #{{ position.num_to_select }} so we will rank {{ nomineesForRanking.length }} nominees.</p>
                <ol class="results">
                    <li v-for="nominee in nomineesForRanking">{{ nominee.name }} ({{ nominee.votes_count}} vote{{ nominee.votes_count != 1 ? 's' : '' }})</li>
                </ol>
            </div>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg" v-if="position.status=='rank' || position.status=='results'">
            <div class="text-xl">Ranking</div>
            <ul>
                <li>Rankings received from: {{ rankersReceived.length }} / {{ voterNumbers.length }}</li>
                <li>Missing rankings: {{ rankersMissing.join(' &nbsp; ') }}</li>
            </ul>
            <ul class="voter-check">
                <li v-for="voter in voterNumbers" :class="{ voted: ranked(voter) }">{{ voter }}</li>
            </ul>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg" v-if="position.status=='results'">
            <div class="text-xl">Results</div>
            <p>
                <a href="/results">Results available! (click here)</a></li>
            </p>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        components: {
        },
        props: [
          'position_id'
        ],
        data: function() {
            return {
              positions: [],
              position: {
                  name: 'Loading...',
                  status: '',
              },
              nomineesForRanking: [],
              voterNumbers: [],
              votersReceived: [],
              rankersReceived: [],
              clearVoterId: 0,
              clearRankerId: 0,
            };
        },
        computed: {
          votersMissing: function() { return this.voterNumbers.filter(a => { return this.votersReceived.indexOf(a.toString()) == -1; }) },
          rankersMissing: function() { return this.voterNumbers.filter(a => { return this.rankersReceived.indexOf(a.toString()) == -1; }) },
        },
        mounted () {
          window.Echo.channel("results-channel").listen(".results-updated", e => {
            console.log({results: e});
            this.voterNumbers = e.voterNumbers,
            this.position = e.position,
            this.votersReceived = e.votersReceived,
            this.rankersReceived = e.rankersReceived,
            this.nomineesForRanking = JSON.parse(e.nomineesForRanking)
          });
          this.updateResults();
          axios
            .get('/api/positions')
            .then(response => this.positions = response.data);
        },
        methods: {
          updateResults() {
            axios
                .get('/api/results')
                .then(response => {
                    this.voterNumbers = response.data.voterNumbers,
                    this.position = response.data.position,
                    this.votersReceived = response.data.votersReceived,
                    this.rankersReceived = response.data.rankersReceived,
                    this.nomineesForRanking = response.data.nomineesForRanking
                });
          },
          voted(voter) {
              return this.votersReceived.indexOf(voter.toString()) != -1;
          },
          ranked(voter) {
              return this.rankersReceived.indexOf(voter.toString()) != -1;
          },
          updatePositionStatus(status) {
            axios
                .put('/api/positions/' + this.position.id, { status: status });
          },
          updatePositionVoteCount() {
            axios
                .put('/api/positions/' + this.position.id, { num_to_select: this.position.num_to_select});
          },
          updateDefaultPosition(new_position) {
            axios
                .put('/api/positions/default', { position_id: new_position });
          },
          clearVotes() {
            axios
                .delete('/api/votes/' + this.position.id + '/' + this.clearVoterId);
	  },
          clearRanks() {
            axios
                .delete('/api/ranks/' + this.position.id + '/' + this.clearRankerId);
	  },
        }
    }
</script>
