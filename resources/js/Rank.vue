<template>
    <div>
        <div class="step-status p-3 sm:px-10 bg-white border-t border-gray-200 fixed bottom-0 left-0 w-full italic bg-yellow-200 z-50" v-if="position.status == 'rank'">
          <p class="text-red italic">Ranking is in progress. When everyone is done ranking, change the current status on the Dashboard page to Results.</p>
        </div>
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
                <p class="my-3">Also note that you can click a name in the "Ranked Nominees" list to open that position and move all later names down, or a blank to move the later names up.</p>
            </div>

            <div v-if="ranksCast">
              Thank you for ranking!
            </div>

              <fieldset v-if="!ranksCast">
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
                <div>
                <div v-if="voter" class="md:flex md:flex-wrap">
                        <div class="flex-1 bg-gray-100 rounded-lg p-2 m-2">
                <ul class="nominees rankings flex flex-wrap" :class="{'mode-increment':rankMode=='increment', 'mode-select':rankMode=='select'}">
                  <li v-for="nominee in nominees" class="w-1/2">
<button class="rank-button" @click="rankChange(nominee.id,-1)">&uarr;</button>
                    <div class="select inline-block relative w-20">
                      <select :id="'vote-' + nominee.id" v-model="ranks[nominee.id]" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                          <option vale=""></option>
                          <option v-for="rank in rankingOptionsLeft(nominee.id)" :value="rank">{{ rank }}</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                      </div>
                    </div><span class="rank-nominee-name ml-1" @click="selectNextRank(nominee.id)">{{ nominee.name }}</span>
<button class="rank-button" @click="rankChange(nominee.id,1)">&darr;</button>
                  </li>
                </ul>
                <p @click="changeRankMode('select')" v-if="rankMode == 'increment'" class="change-mode">Change to Select List Mode</p>
                <p @click="changeRankMode('increment')" v-if="rankMode == 'select'" class="change-mode">Change to Increment Mode</p>
                        </div>
                        <div class="flex-1 bg-gray-200 rounded-lg p-2 m-2">

                Ranked Nominees:
                <ol class="ranked">
                  <li v-for="(nominee, i) in nominees" @click="shiftDown(i+1)">{{ getNomineeByRank(i+1) }}</li>
                </ol>

                <ul class="rank-summary" style="display: none;">
                  <li>
                    <strong>{{ ranksCompleted }}</strong> nominees ranked
                  </li>
                  <li>
                    <strong :class="{ 'done-voting': ranksRemaining == 0 }">{{ ranksRemaining }}</strong> selections remaining
                  </li>
                </ul>

                <p class="warning bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" v-if="ranksRemaining > 0">You must rank all nominees before submitting. You still need to rank {{ ranksRemaining }} more nominee{{ ranksRemaining !== 1 ? 's' : '' }}.</p>

                <button
                    @click="postVotes"
                    :disabled="!canSubmit"
                    :class="{'bg-blue-500 hover:bg-blue-700': canSubmit, 'bg-gray-300 cursor-not-allowed': !canSubmit}"
                    class="text-white font-bold py-2 px-4 my-2 rounded mb-20"
                >
                    Submit Rankings
                </button>
                </div>
                </div>
                </div>
              </fieldset>
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
              voterNumbers: [],
              ranks: [],
              ranksCast: false,
	      rankMode: 'increment',
              voter: 0,
              nominees: [
              ]
            };
        },
        watch: {
          voter: function(newVoter, oldVoter) {
            if(!newVoter || newVoter == 'undefined') {
              return;
            }
            this.$cookies.set('voter',newVoter);
            this.$root.voter = newVoter;
          },
        },
        computed: {
          ranksCompleted: function() { return this.ranks.filter(r => r).length; },
          ranksRemaining: function() { return this.nominees.length - this.ranks.filter(r => r).length; },
          rankingOptions: function() { return Array.from({length: this.nominees.length}, (_, i) => i + 1) },
          canSubmit () { return this.ranksRemaining === 0; },
        },
        mounted () {
          window.Echo.channel("position-channel").listen(".position-updated", e => {
            this.position = e.position;
            this.navigateAway();
          });
          this.updatePosition();

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
        methods: {
          getSavedVoter() {
            var voter_cookie = this.$cookies.get('voter');
            if(this.voter === 0 && (voter_cookie || this.$root.voter)) {
              this.voter = voter_cookie || this.$root.voter;
            }
          },
          setSavedVoter(newVoter, oldVoter) {
            this.$root.voter = newVoter;
          },
          updatePosition() {
            axios
              .get('/api/positions/' + this.position_id)
              .then(response => {
                this.position = response.data;
                this.navigateAway();
              });
          },
          navigateAway() {
            if(this.position.status != 'rank') {
              console.log('navigating from rank to ' + this.position.status);
              this.$inertia.visit('/' + this.position.status);
            }
          },
          postVotes() {
            // Validate that all nominees are ranked
            if (!this.canSubmit) {
              alert(`You must rank all nominees before submitting. You still need to rank ${this.ranksRemaining} more nominee${this.ranksRemaining !== 1 ? 's' : ''}.`);
              return;
            }

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
            // Clear the nominee's rank if one is already set
            if(this.ranks[nominee_id]) {
this.ranks[nominee_id] = null;
            } else {

	      // get the next available option
	      var next_rank = this.rankingOptions.reduce((min, cur) => { return cur < min && this.ranks.indexOf(cur) == -1 ? cur : min },100);

	      // set the ranking
	      this.ranks[nominee_id] = next_rank;
	    }

            // force UI update to fix select checked options
            this.$forceUpdate();
          },
	  rankChange(nominee_id,change) {
	    let rank = this.ranks[nominee_id];
	    let new_rank = rank + change;
	    if(!rank) {
	      return this.selectNextRank(nominee_id);
	    }
	    for(var i in this.ranks) {
	      if(this.ranks[i] == rank) {
	        this.ranks[i] += change;
	      } else if(this.ranks[i] == new_rank) {
		this.ranks[i] += -1*change;
	      }
	    }
            this.$forceUpdate();
	  },
	  changeRankMode(new_mode) {
	    this.rankMode = new_mode;
	  },
	  shiftDown(rank) {
	    let shift = this.ranks.includes(rank) ? 1 : -1; // if the rank is filled, move down; else move up
	    for(var i in this.ranks) {
	      if(this.ranks[i] >= rank) {
	        this.ranks[i] += shift;
	      }
	    }
            this.$forceUpdate();
	  }
        }
    }
</script>
