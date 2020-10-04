<template>
    <div>
        <div class="pt-6 sm:px-20 bg-white rounded-t-lg">
            <div class="mt-8 text-2xl">
                FCC Nominations Dashboard!
            </div>

            <div class="mt-6 text-gray-500">
                Here you will find the current status of the nominating process.
            </div>

            <p>Current Position: {{ position.name }}</p>
            <p>Current Status: {{ position.status }}</p>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl mb-10 rounded-b-lg" v-if="$page.user">
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
              voterNumbers: [],
              votersReceived: [],
              rankersReceived: []
            };
        },
        computed: {
          votersMissing: function() { return this.voterNumbers.filter(a => { return this.votersReceived.indexOf(a.toString()) == -1; }) },
          rankersMissing: function() { return this.voterNumbers.filter(a => { return this.rankersReceived.indexOf(a.toString()) == -1; }) },
        },
        mounted () {
          clearInterval(window.fccUpdateInterval);
          this.updateResults(this);
          window.fccUpdateInterval = setInterval(() => this.updateResults(this), 5000);

          axios
            .get('/api/positions')
            .then(response => this.positions = response.data);
        },
        methods: {
          updateResults(vue) {
            axios
                .get('/api/results')
                .then(response => {
                    vue.voterNumbers = response.data.voterNumbers,
                    vue.position = response.data.position,
                    vue.votersReceived = response.data.votersReceived,
                    vue.rankersReceived = response.data.rankersReceived
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
                .put('/api/positions/' + this.position.id, { status: status })
                .then(response => {
                    this.position = response.data;
                })
          },
          updateDefaultPosition(new_position) {
            axios
                .put('/api/positions/default', { position_id: new_position })
                .then(response => {
                    this.updateResults();
                })
          }
        }
    }
</script>