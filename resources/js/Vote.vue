<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="mt-8 text-2xl">
                Welcome to FCC Nominations!
            </div>

            <div class="mt-6 text-gray-500">
                Here we will follow the 2-step process for selecting our nominees: vote then rank.
            </div>

            <div v-if="votesCast">
              Thank you for voting!
            </div>
            <form v-if="!votesCast">
            	<fieldset>
                <select name="voter-number" v-model="voter">
                  <option value="">-- Select your voter number --</option>
                  <option :value="voter" v-for="voter in voterNumbers">{{ voter }}</option>
                </select>
                <div v-if="voter">
                <ul class="nominees">
                  <li v-for="nominee in nominees">
                    <label class="checkmark-container">{{ nominee.name }} <input type="checkbox" :id="'vote-' + nominee.id" :value="nominee.id" v-model="votes"><span class="checkmark"></span></label>
                  </li>
                </ul>
                <ul class="vote-summary">
                  <li>
                    <strong>{{ votes.length }}</strong> nominees selected
                  </li>
                  <li>
                    <strong :class="{ 'done-voting': votesRemaining == 0 }">{{ votesRemaining }}</strong> selections remaining
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
        data: function() {
            return {
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
          canSubmit () { return this.votes.length > 0; },
        },
        mounted () {
          axios
            .get('/api/positions/' + this.position_id)
            .then(response => {
              this.position = response.data;
              this.votesAllowed = response.data.num_to_select;
            });
          axios
            .get('/api/voters/' + this.position_id)
            .then(response => (this.voterNumbers = response.data));
          axios
            .get('/api/nominations?position_id=' + this.position_id)
            .then(response => (this.nominees = response.data));
        },
        methods: {
          postVotes() {
            axios.post('/api/votes', {
              voter: this.voter,
              votes: this.votes,
              position_id: this.nominees[0].position_id,
            })
            .then(resp => {
              console.log(resp);
              this.votesCast = true;
            })
          }
        }
    }
</script>
