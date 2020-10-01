<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="mt-8 text-2xl">
                Welcome to FCC Nominations!
            </div>

            <div class="mt-6 text-gray-500">
                We have our results! Below is the ranked list of nominees for this position.
            </div>

            <ol class="nominees">
                <li v-for="nominee in nominees">
                {{ nominee.name }}
                </li>
            </ol>
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
              nominees: [
              ]
            };
        },
        mounted () {
          axios
            .get('/api/results')
            .then(response => (this.nominees = response.data));
        },
        methods: {
        }
    }
</script>
