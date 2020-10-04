<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="text-gray-500" v-if="position.status == ''">
              Loading...
            </div>
            <div class="text-gray-500" v-else-if="position.status == 'vote'">
              Voting is still in progress, so it's not time to rank or view results yet. <inertia-link href="/vote">If you haven't voted, click here to vote now!</inertia-link>
            </div>
            <div class="text-gray-500" v-else-if="position.status == 'rank'">
              Ranking is still in progress, so it's not time to view results yet. <inertia-link href="/rank">If you haven't ranked nominees yet, click here to rank them now!</inertia-link>
            </div>
            <div v-else-if="position.status == 'results'">
            <div class="text-gray-500">
                We have our results! Below is the ranked list of nominees for this position.
            </div>

            <ol class="results">
                <li v-for="nominee in nominees">
                {{ nominee.nomination.last_name }}, {{ nominee.nomination.first_name }}<span v-if="$page.user"> ({{ nominee.sum }})</span>
                </li>
            </ol>
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
          'position_id',
          'nominees'
        ],
        data: function() {
            return {
              position: {
                status: ''
              },
              updatePositionInterval: null
            };
        },
        mounted () {
          clearInterval(window.fccUpdateInterval);
          this.updatePosition(this);
          window.fccUpdateInterval = setInterval(() => this.updatePosition(this), 5000);
        },
        methods: {
          updatePosition(vue) {
            axios
              .get('/api/positions/' + vue.position_id)
              .then(response => {
                vue.position = response.data;
              });
          },
        }
    }
</script>
