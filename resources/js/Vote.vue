<template>
    <div class="vote">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <div class="text-gray-500" v-if="position.status == ''">
            Loading...
          </div>
          <div class="text-gray-500" v-else-if="position.status == 'rank'">
            {{ position.name }} voting is closed and ranking has started. <inertia-link href="/rank">If you haven't ranked nominees yet, click here to rank them now!</inertia-link>
          </div>
          <div class="text-gray-500" v-else-if="position.status == 'results'">
            <inertia-link href="/results">{{ position.name }} results are ready! Click to view the results.</inertia-link>
          </div>
          <div v-else-if="position.status == 'vote'">
            <div class="text-gray-500">
                Select {{ votesAllowed }} {{ position.name }} nominees below to start the process.
            </div>

            <div v-if="votesCast">
              Thank you for voting for {{ position.name }}!
            </div>
            <div v-if="!votesCast">
            	<fieldset>
                <label for="voter">Select Your Voter Number:</label>

                <div class="inline-block relative w-20">
                  <select name="voter-number" v-model="voter" v-if="voterNumbers" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">-- Select Your voter number --</option>
                    <option :value="voter" v-for="voter in voterNumbers">{{ voter }}</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                  </div>
                </div>
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
                <ul class="vote-summary bg-yellow-200 p-3">
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
    import Vue from 'vue';
    import VueCookies from 'vue-cookies';
    Vue.use(VueCookies);

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
        watch: {
          voter: function(newVoter, oldVoter) {
            this.$cookies.set('voter',newVoter);
          },
        },
        computed: {
          votesRemaining: function() { return this.votesAllowed - this.votes.length; },
          canSubmit () { return this.votes.length > 0 && this.votes.length <= this.votesAllowed; },
        },
        mounted () {
          window.Echo.channel("position-channel").listen(".position-updated", e => {
            this.position = e.position;
            this.navigateAway();
          });
          this.updatePosition();

          axios
            .get('/api/voters/' + this.position_id)
            .then(response => {
              this.voterNumbers = response.data;
              this.getSavedVoter();
            });
          axios
            .get('/api/nominations?position_id=' + this.position_id)
            .then(response => (this.nominees = response.data));
        },
        methods: {
          getSavedVoter() {
            var voter_cookie = this.$cookies.get('voter');
            if(this.voter === 0 && voter_cookie) {
              this.voter = voter_cookie;
            }
          },
          navigateAway() {
            if(this.position.status != 'vote') {
              this.$inertia.visit('/' + this.position.status);
            }
          },
          updatePosition() {
            axios
              .get('/api/positions/' + this.position_id)
              .then(response => {
                this.position = response.data;
                this.navigateAway();
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
