<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="text-gray-500" v-if="position.status == ''">
              Loading...
            </div>
            <div class="text-gray-500" v-else-if="position.status == 'vote'">
              Voting is still in progress, so it's not time to rank yet. <inertia-link href="/vote">If you haven't voted, click here to vote now!</inertia-link>
            </div>
            <div class="text-gray-500" v-else-if="position.status == 'results'">
              <inertia-link href="/results">Results are ready! Click to view the results.</inertia-link>
            </div>
            <div v-else-if="position.status == 'rank'">
            <div class="text-gray-500">
                <p>Now that all the votes are in, we have our top nominees. Next you will rank these nominees from the your first/top/lowest number pick to your last/bottom/highest number.</p>
                <p class="my-3">There are two different ways to make your rank selections:
                  <ol class="ranked">
                    <li>Make your selections from the select lists/dropdowns</li>
                    <li>Click the names of the nominees in order from your top pick to last pick</li>
                  </ol>
                </p>
            </div>

            <div v-if="ranksCast">
              Thank you for ranking!
            </div>

            <form v-if="!ranksCast">
            	<fieldset>
                    <label for="voter">Select Your Voter Number:</label>
                    <select name="voter-number" v-model="voter" v-if="voterNumbers">
                        <option value="">-- Select Your voter number --</option>
                        <option :value="voter_num" v-for="voter_num in voterNumbers">{{ voter_num }}</option>
                    </select>
                    <div>
                <div v-if="voter" class="md:flex md:flex-wrap">
                        <div class="flex-1 bg-gray-100 rounded-lg p-2 m-2">
                <ul class="nominees rankings flex flex-wrap">
                  <li v-for="nominee in nominees" class="w-1/2">
                    <select :id="'vote-' + nominee.id" v-model="ranks[nominee.id]">
                        <option vale=""></option>
                        <option v-for="rank in rankingOptionsLeft(nominee.id)" :value="rank">{{ rank }}</option>
                    </select><span class="rank-nominee-name ml-1" @click="selectNextRank(nominee.id)">{{ nominee.name }}</span>
                  </li>
                </ul>
                        </div>
                        <div class="flex-1 bg-gray-200 rounded-lg p-2 m-2">

                Ranked Nominees:
                <ol class="ranked">
                  <li v-for="(nominee, i) in nominees">{{ getNomineeByRank(i+1) }}</li>
                </ol>

                <ul class="rank-summary" style="display: none;">
                  <li>
                    <strong>{{ ranksCompleted }}</strong> nominees ranked
                  </li>
                  <li>
                    <strong :class="{ 'done-voting': ranksRemaining == 0 }">{{ ranksRemaining }}</strong> selections remaining
                  </li>
                </ul>

                <p class="warning bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" v-if="ranksRemaining < 0">You must rank all nominees before submitting. You're still missing {{ rankingsRemaining }} rankings.</p>

                <input type="submit" value="Submit Rankings" @click="postVotes" @click.prevent="!canSubmit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 rounded" />
                </div>
                </div>
                </div>
              </fieldset>
            </form>
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
              updatePositionInterval: null,
              voterNumbers: [],
              ranks: [],
              ranksCast: false,
              voter: 0,
              nominees: [
              ]
            };
        },
        watch: {
          voter: function(newVoter, oldVoter) {
            this.$cookies.set('voter',newVoter);
          },
        },
        computed: {
          ranksCompleted: function() { return this.ranks.filter(r => r).length; },
          ranksRemaining: function() { return this.nominees.length - this.ranks.filter(r => r).length; },
          rankingOptions: function() { return Array.from({length: this.nominees.length}, (_, i) => i + 1) },
          canSubmit () { this.ranks.filter(r => r === null).length == 0; },
        },
        mounted () {
          clearInterval(window.fccUpdateInterval);
          this.updatePosition(this);
          window.fccUpdateInterval = setInterval(() => this.updatePosition(this), 5000);
          axios
            .get('/api/rankers/' + this.position_id)
            .then(response => {
              this.voterNumbers = response.data;
              this.getSavedVoter();
            });
          axios
            .get('/api/ranks/nominees/' + this.position_id)
            .then(response => (this.nominees = response.data));
        },
        destroyed() {
          clearInterval(this.updatePositionInterval);
        },
        methods: {
          getSavedVoter() {
            var voter_cookie = this.$cookies.get('voter');
            if(this.voter === 0 && voter_cookie) {
              this.voter = voter_cookie;
            }
          },
          setSavedVoter(newVoter, oldVoter) {
            this.$root.voter = newVoter;
          },
          updatePosition(vue) {
            axios
              .get('/api/positions/' + vue.position_id)
              .then(response => {
                vue.position = response.data;

                if(response.data.status == 'results') {
                  vue.$inertia.visit('/results');
                }
              });
          },
          postVotes() {
            var filtered_ranks = this.ranks.map((val, i) => (val == null ? null : {id:i,rank:val})).filter(val => val != null );
            axios.post('/api/ranks', {
              voter: this.voter,
              ranks: filtered_ranks,
              position_id: this.nominees[0].position_id,
            })
            .then(resp => {
              this.ranksCast = true;
            })
          },
          getNomineeByRank(i) {
            var nominee_id = this.ranks.indexOf(i);
            if(nominee_id == -1) {
              return '';
            }

            var nominee = this.nominees.find(n => { return n.id == nominee_id });

            return nominee.name;
          },
          rankingOptionsLeft: function(nominee_id) {
            return this.rankingOptions.filter(o => { return this.ranks.indexOf(o) == -1 || [this.ranks[nominee_id],''].indexOf(o) != -1 });
          },
          getNextAvailableRank() {
          },
          selectNextRank(nominee_id) {
            // Don't change the rank if one is already set
            if(this.ranks[nominee_id]) {
              return;
            }

            // get the next available option
            var next_rank = this.rankingOptions.reduce((min, cur) => { return cur < min && this.ranks.indexOf(cur) == -1 ? cur : min },100);

            // set the ranking
            this.ranks[nominee_id] = next_rank;

            // force UI update to fix select checked options
            this.$forceUpdate();
          }
        }
    }
</script>
