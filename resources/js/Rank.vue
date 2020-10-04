<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="mt-8 text-2xl">
                Welcome to FCC Nominations!
            </div>

            <div class="mt-6 text-gray-500">
                Now that all the votes are in, we have our top nominees. Next you will rank these nominees from the your first/top/lowest number pick to your last/bottom/highest number.
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
                        <option v-for="rank in rankingOptions" :value="rank">{{ rank }}</option>
                    </select> {{ nominee.name }}
                  </li>
                </ul>
                        </div>
                        <div class="flex-1 bg-gray-200 rounded-lg p-2 m-2">

                Ranked Nominees:
                <ol class="ranked">
                  <li v-for="(nominee, i) in nominees">{{ getNomineeNameByRank(i+1) }}</li>
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
</template>

<script>
    import axios from 'axios';

    export default {
        components: {
        },
        props: [
          'position_id',
          'voter'
        ],
        data: function() {
            return {
              voterNumbers: [],
              ranks: [],
              ranksCast: false,
              nominees: [
              ]
            };
        },
        computed: {
          ranksCompleted: function() { return this.ranks.filter(r => r).length; },
          ranksRemaining: function() { return this.nominees.length - this.ranks.filter(r => r).length; },
          rankingOptions: function() { return Array.from({length: this.nominees.length}, (_, i) => i + 1) },
          canSubmit () { this.ranks.filter(r => r === null).length == 0; },
        },
        mounted () {
          axios
            .get('/api/rankers/' + this.position_id)
            .then(response => (this.voterNumbers = response.data));
          axios
            .get('/api/ranks/nominees/' + this.position_id)
            .then(response => (this.nominees = response.data));
        },
        methods: {
          postVotes() {
            axios.post('/api/ranks', {
              voter: this.voter,
              ranks: this.ranks,
              position_id: this.nominees[0].position_id,
            })
            .then(resp => {
              console.log(resp);
              this.ranksCast = true;
            })
          },
          getNomineeByRank(i) {
              var nominees = document.querySelectorAll('select option:checked[value="' + i + '"]');
              var names = []

              if(!nominees.length) {
                  return [];
              }

              nominees.forEach(nominee => {
                  var name = nominee.parentElement.nextSibling.textContent.trim();
                  if(name.length) {
                    names.push(name);
                  }
              });

              return names;
          },
          getNomineeNameByRank(i) {
              var names = this.getNomineeByRank(i);
              return names.join(' AND ')
          }
        }
    }
</script>
