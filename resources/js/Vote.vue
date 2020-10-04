<template>
    <div class="vote">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="mt-8 text-2xl">
                Welcome to FCC Nominations!
            </div>

          <div class="mt-6 text-gray-500" v-if="position.status == ''">
            Loading...
          </div>
          <div class="mt-6 text-gray-500" v-else-if="position.status == 'rank'">
            Voting is closed and ranking has started. <inertia-link href="/rank">If you haven't ranked nominees yet, click here to rank them now!</inertia-link>
          </div>
          <div class="mt-6 text-gray-500" v-else-if="position.status == 'results'">
            <inertia-link href="/results">Results are ready! Click to view the results.</inertia-link>
          </div>
          <div v-else-if="position.status == 'vote'">
            <div class="mt-6 text-gray-500">
                Here we will follow the 2-step process for selecting our nominees: vote then rank.
            </div>

            <div v-if="votesCast">
              Thank you for voting!
            </div>
            <div v-if="!votesCast">
            	<fieldset>
                <label for="voter">Select Your Voter Number:</label>
                <select name="voter-number" v-model="voter" v-if="voterNumbers">
                  <option value="">-- Select Your voter number --</option>
                  <option :value="voter" v-for="voter in voterNumbers">{{ voter }}</option>
                </select>
                <div v-if="voter">
                <ul class="nominees">
                  <li v-for="nominee in nominees">
                    <label class="checkmark-container">
                      {{ nominee.name }}
                      <input type="checkbox" :id="'vote-' + nominee.id" :value="nominee.id" v-model="votes">
                      <span class="checkmark"></span>
                    </label>
                  </li>
                </ul>
                <ul class="vote-summary bg-gray-400 p-3">
                  <li>
                    <strong>{{ votes.length }}</strong> nominees selected
                  </li>
                  <li>
                    <strong :class="{ 'done-voting': votesRemaining == 0 }">{{ votesRemaining }}</strong> selections remaining
                  </li>
                </ul>
                <p class="warning bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" v-if="votesRemaining < 0">You have selected too many nominees. Please remove {{ -1*votesRemaining }} before casting your votes.</p>
                <input type="submit" value="Vote" @click="postVotes" :disabled="!canSubmit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 rounded" />
                </div>
              </fieldset>
            </div>
          </div>
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
              position: {
                status: ''
              },
              updatePositionInterval: null,
              votesAllowed: 10,
              voterNumbers: [],
              votes: [],
              voter: 0,
              votesCast: false,
              votersCompleted: [],
              nominees: [
              ]
            };
        },
        props: [
          'position_id'
        ],
        computed: {
          votesRemaining: function() { return this.votesAllowed - this.votes.length; },
          canSubmit () { return this.votes.length > 0 && this.votes.length <= this.votesAllowed; },
        },
        mounted () {
          this.updatePosition(this);
          this.updatePositionInterval = setInterval(() => this.updatePosition(this), 5000);
          axios
            .get('/api/voters/' + this.position_id)
            .then(response => (this.voterNumbers = response.data));
          axios
            .get('/api/nominations?position_id=' + this.position_id)
            .then(response => (this.nominees = response.data));
        },
        destroyed() {
          clearInterval(this.updatePositionInterval);
        },
        methods: {
          updatePosition(vue) {
            axios
              .get('/api/positions/' + vue.position_id)
              .then(response => {
                vue.position = response.data;
                vue.votesAllowed = response.data.num_to_select;

                if(vue.position.status == 'rank') {
                  this.$inertia.visit('/rank');
                }
              });
          },
          postVotes() {
            axios.post('/api/votes', {
              voter: this.voter,
              votes: this.votes,
              position_id: this.nominees[0].position_id,
            })
            .then(resp => {
              this.votesCast = true;
            })
          }
        }
    }
</script>
