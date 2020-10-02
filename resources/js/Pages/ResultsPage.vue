<template>
    <app-layout-no-auth>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
               Results
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <results :position_id="$page['position_id']" :nominees="$page['nominees']" />
                </div>
            </div>
        </div>
    </app-layout-no-auth>
</template>

<script>
    import AppLayoutNoAuth from './../Layouts/AppLayoutNoAuth'
    import Results from './../Results'

    export default {
        components: {
            AppLayoutNoAuth,
            Results,
        },
    }
</script>
