<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
               Rank
            </h2>
        </template>

        <div class="p-6 sm:p-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <rank :position_id="$page['position_id']" :voter="$page['voter_id']" />
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'
    import Rank from './../Rank'

    export default {
        components: {
            AppLayout,
            Rank,
        },
    }
</script>
