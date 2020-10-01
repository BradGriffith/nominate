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
                    <label for="voter">Select your Voter Number:</label>
                    <select name="voter-number" v-model="voter">
                        <option value="">-- Select your voter number --</option>
                        <option :value="voter" v-for="voter in voterNumbers">{{ voter }}</option>
                    </select>
                <div v-if="voter">
                <ul class="nominees">
                  <li v-for="nominee in nominees">
                    <select :id="'vote-' + nominee.id" v-model="ranks">
                        <option vale=""></option>
                        <option v-for="rank in rankingOptions" :value="rank">{{ rank }}</option>
                    </select> {{ nominee.name }}
                  </li>
                </ul>
                <ul class="rank-summary">
                  <li>
                    <strong>{{ ranks.length }}</strong> nominees ranked
                  </li>
                  <li>
                    <strong :class="{ 'done-voting': ranksRemaining == 0 }">{{ ranksRemaining }}</strong> selections remaining
                  </li>
                </ul>
                <input type="submit" value="Vote" @click="postVotes" @click.prevent="!canSubmit" />
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
              ranksCompleted: [],
              nominees: [
              ]
            };
        },
        computed: {
          ranksRemaining: function() { return this.nominees.length - this.ranks.length; },
          rankingOptions: function() { return Array.from({length: this.nominees.length}, (_, i) => i + 1) },
          canSubmit () { return this.ranks.length > 0; },
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
          }
        }
    }
</script>
