<template>
    <div>
        <div class="p-3 sm:px-10 bg-white border-t border-gray-200 fixed bottom-0 left-0 w-full italic bg-yellow-200 z-50">
          <p v-if="position.status == 'vote'">Voting is in progress. When everyone is done voting, click <span class="text-blue-600 font-bold">→</span> to change the status to rank.</p>
          <p v-if="position.status == 'rank'">Ranking is in progress. When everyone is done ranking, click <span class="text-blue-600 font-bold">→</span> above to show results.</p>
          <p v-if="position.status == 'results'">Results are complete. To vote on a different position, click the name of the position in the Admin Controls.</p>
        </div>
        <div class="p-6 sm:px-20 bg-white rounded-t-lg">
            <div class="text-gray-500">
                Here you will find the current status of the nominating process.
            </div>

            <p>Current Position: {{ position.name }}</p>
            <p class="flex items-center gap-2">
                <span>Current Status:</span>
                <button
                    v-if="position.status != 'vote'"
                    @click="moveToPreviousStatus"
                    class="px-2 py-1 text-xl font-bold rounded text-blue-600 hover:bg-blue-100"
                    title="Move to previous status"
                >
                    ←
                </button>
                <span class="font-semibold">{{ position.status }}</span>
                <button
                    v-if="position.status != 'results'"
                    @click="moveToNextStatus"
                    class="px-2 py-1 text-xl font-bold rounded text-blue-600 hover:bg-blue-100"
                    title="Move to next status"
                >
                    →
                </button>
            </p>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg" v-if="position.status=='rank' || position.status=='results'">
            <div class="text-xl">Ranking</div>
            <ul>
                <li>Rankings received from: {{ rankersReceived.length }} / {{ voterNumbers.length }}</li>
                <li>Missing rankings: {{ rankersMissing.join(' &nbsp; ') }}</li>
            </ul>
            <ul class="voter-check">
                <li v-for="voter in voterNumbers" :class="{ voted: ranked(voter) }">{{ voter }}</li>
            </ul>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg">
            <div class="text-xl">Voting</div>
            <div v-if="position.status.length">
                <ul>
                    <li>Votes received from: {{ votersReceived.length }} / {{ voterNumbers.length }}</li>
                    <li>Missing votes: {{ votersMissing.join(' &nbsp; ') }}</li>
                </ul>
                <ul class="voter-check">
                    <li v-for="voter in voterNumbers" :class="{ voted: voted(voter) }">{{ voter }}</li>
                </ul>
                <p class="font-bold pt-6">Nominees for ranking:</p>
                <p class="font-italic pb-2 italic" v-if="nomineesForRanking.length > position.num_to_select">There was a tie for #{{ position.num_to_select }} so we will rank {{ nomineesForRanking.length }} nominees.</p>
                <ol class="results">
                    <li v-for="nominee in nomineesForRanking">{{ nominee.name }} ({{ nominee.votes_count}} vote{{ nominee.votes_count != 1 ? 's' : '' }})</li>
                </ol>
            </div>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg" v-if="position.status=='results'">
            <div class="text-xl">Results</div>
            <p>
                <a href="/results">Results available! (click here)</a></li>
            </p>
        </div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200 shadow-xl my-10 rounded-lg" v-if="$page.user">
            <div class="text-xl cursor-pointer flex justify-between items-center" @click="adminControlsExpanded = !adminControlsExpanded">
                <span>Admin Controls</span>
                <span class="text-2xl">{{ adminControlsExpanded ? '−' : '+' }}</span>
            </div>
            <div v-show="adminControlsExpanded" class="mt-4">
                <div class="text-l inline clear">Change Status: </div>
                <div class="inline-flex" v-if="position.status == 'vote'">
                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r" @click="updatePositionStatus('rank')">
                        Start Ranking &raquo;
                    </button>
                </div>
                <div class="inline-flex" v-if="position.status == 'rank'">
                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l" @click="updatePositionStatus('vote')">
                        &laquo; Re-open Voting
                    </button>
                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r  border-l border-gray-200" @click="updatePositionStatus('results')">
                        Stop Ranking and Show Final Results &raquo;
                    </button>
                </div>
                <div class="inline-flex" v-if="position.status == 'results'">
                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l" @click="updatePositionStatus('vote')">
                        &laquo; Re-open Voting
                    </button>
                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r  border-l border-gray-200" @click="updatePositionStatus('rank')">
                        &laquo; Re-open Ranking
                    </button>
                </div>
                <div class="p-1"></div>
                <div class="text-l inline">Change Position: </div>
                <div class="inline-flex">
                    <button :class="['py-2 px-4 mx-2',{'current text-gray-400': pos.id == position.id, 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold': pos.id != position.id}]" @click="updateDefaultPosition(pos.id)" v-for="pos in positions">
                        {{ pos.name }}
                    </button>
                </div>
                <div class="p-1"></div>
                <div class="text-l inline">Change Allowed Votes/Ranks Per Person: </div>
                <div class="inline-flex">
                    <select v-model="position.num_to_select">
                        <option v-for="i in 48">{{ i }}</option>
                    </select>
                    <button class="py-2 px-4 mx-2 bg-orange-400 hover:bg-orange-600 text-gray-200 font-bold" @click="updatePositionVoteCount">
                        Change {{ position.name }} Votes/Ranks Allowed
                    </button>
                </div>
                <div v-if="votersReceived.length">
                    <div class="p-1"></div>
                    <div class="text-l inline">Clear Votes: </div>
                    <div class="inline-flex">
                        <select v-model="clearVoterId">
                            <option value="all">All</option>
                            <option v-for="voter in votersReceived">{{ voter }}</option>
                        </select>
                        <button class="py-2 px-4 mx-2 bg-red-700 hover:bg-red-400 text-gray-200 font-bold" @click="confirmClearVotes">
                            Clear {{ position.name }} Votes
                        </button>
                    </div>
                </div>
                <div v-if="rankersReceived.length">
                    <div class="p-1"></div>
                    <div class="text-l inline">Clear Ranks: </div>
                    <div class="inline-flex">
                        <select v-model="clearRankerId">
                            <option value="all">All</option>
                            <option v-for="ranker in rankersReceived">{{ ranker }}</option>
                        </select>
                        <button class="py-2 px-4 mx-2 bg-red-700 hover:bg-red-400 text-gray-200 font-bold" @click="confirmClearRanks">
                            Clear {{ position.name }} Ranks
                        </button>
                    </div>
                </div>
                <div class="border-t border-gray-300 my-6"></div>
                <div class="text-lg mb-4 font-bold">Manage Nominees</div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Position:</label>
                    <select v-model="managePositionId" class="block w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select Position --</option>
                        <option v-for="pos in positions" :key="pos.id" :value="pos.id">{{ pos.name }}</option>
                    </select>
                </div>
                <div v-if="managePositionId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Add New {{ selectedPositionName }} Nominees (one per line, format: "Last, First" or "First Last"):
                        </label>
                        <textarea
                            v-model="nomineesText"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            rows="10"
                            placeholder="Smith, John
Jane Doe
Brown, Alice"
                        ></textarea>
                    </div>
                    <div class="mb-6">
                        <button
                            @click="confirmImportNominees"
                            :disabled="!nomineesText.trim()"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:bg-gray-300 disabled:cursor-not-allowed"
                        >
                            Add Nominees
                        </button>
                    </div>
                    <div v-if="importMessage" class="p-4 rounded-md mb-6" :class="{'bg-green-100 border border-green-400 text-green-700': importSuccess, 'bg-red-100 border border-red-400 text-red-700': !importSuccess}">
                        <p class="font-bold">{{ importMessage }}</p>
                        <ul v-if="importErrors.length" class="list-disc list-inside mt-2">
                            <li v-for="error in importErrors" :key="error">{{ error }}</li>
                        </ul>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remove {{ selectedPositionName }} Nominee:</label>
                        <div class="flex items-center gap-2">
                            <select v-model="removeNomineeId[managePositionId]" class="block w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Select Nominee to Remove --</option>
                                <option v-for="nominee in nomineesByPosition[managePositionId]" :key="nominee.id" :value="nominee.id">
                                    {{ nominee.last_name }}, {{ nominee.first_name }}
                                </option>
                            </select>
                            <button
                                @click="confirmRemoveNominee(managePositionId)"
                                :disabled="!removeNomineeId[managePositionId]"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:bg-gray-300 disabled:cursor-not-allowed whitespace-nowrap"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Nominees Confirmation Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showImportModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Add</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            You are about to add <strong>{{ importPreview.toImport }}</strong> nominee{{ importPreview.toImport !== 1 ? 's' : '' }}.
                        </p>
                        <p v-if="importPreview.duplicates > 0" class="text-sm text-gray-500 mt-2">
                            <strong>{{ importPreview.duplicates }}</strong> duplicate{{ importPreview.duplicates !== 1 ? 's' : '' }} will be skipped.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center px-4 py-3">
                        <button
                            @click="showImportModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="proceedWithImport"
                            class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none"
                        >
                            Add Nominees
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Remove Confirmation Modal -->
        <div v-if="showRemoveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showRemoveModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Removal</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to remove <strong>{{ nomineeToRemoveName }}</strong>?
                        </p>
                        <p v-if="nomineeHasVotesOrRanks" class="text-sm text-red-600 mt-2">
                            All votes and ranks for this nominee will also be removed.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center px-4 py-3">
                        <button
                            @click="showRemoveModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="removeNominee"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Clear Votes Confirmation Modal -->
        <div v-if="showClearVotesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showClearVotesModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Clear Votes</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to clear <strong>{{ clearVoterId === 'all' ? 'all' : 'voter ' + clearVoterId }}</strong> votes for <strong>{{ position.name }}</strong>?
                        </p>
                        <p class="text-sm text-red-600 mt-2">
                            This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center px-4 py-3">
                        <button
                            @click="showClearVotesModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="clearVotes"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none"
                        >
                            Clear Votes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Clear Ranks Confirmation Modal -->
        <div v-if="showClearRanksModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showClearRanksModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Clear Ranks</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to clear <strong>{{ clearRankerId === 'all' ? 'all' : 'ranker ' + clearRankerId }}</strong> ranks for <strong>{{ position.name }}</strong>?
                        </p>
                        <p class="text-sm text-red-600 mt-2">
                            This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center px-4 py-3">
                        <button
                            @click="showClearRanksModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="clearRanks"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none"
                        >
                            Clear Ranks
                        </button>
                    </div>
                </div>
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
          'position_id'
        ],
        data: function() {
            return {
              positions: [],
              position: {
                  name: 'Loading...',
                  status: '',
              },
              nomineesForRanking: [],
              voterNumbers: [],
              votersReceived: [],
              rankersReceived: [],
              clearVoterId: 0,
              clearRankerId: 0,
              managePositionId: '',
              nomineesText: '',
              importMessage: '',
              importSuccess: false,
              importErrors: [],
              adminControlsExpanded: false,
              nomineesByPosition: {},
              removeNomineeId: {},
              showRemoveModal: false,
              nomineeToRemove: null,
              nomineeToRemoveName: '',
              nomineeHasVotesOrRanks: false,
              showImportModal: false,
              importPreview: { toImport: 0, duplicates: 0 },
              showClearVotesModal: false,
              showClearRanksModal: false,
            };
        },
        computed: {
          votersMissing: function() { return this.voterNumbers.filter(a => { return this.votersReceived.indexOf(a.toString()) == -1; }) },
          rankersMissing: function() { return this.voterNumbers.filter(a => { return this.rankersReceived.indexOf(a.toString()) == -1; }) },
          selectedPositionName: function() {
            const position = this.positions.find(p => p.id == this.managePositionId);
            return position ? position.name : '';
          },
        },
        mounted () {
          window.Echo.channel("results-channel").listen(".results-updated", e => {
            console.log({results: e});
            this.voterNumbers = e.voterNumbers,
            this.position = e.position,
            this.votersReceived = e.votersReceived,
            this.rankersReceived = e.rankersReceived,
            this.nomineesForRanking = JSON.parse(e.nomineesForRanking)
          });
          this.updateResults();
          axios
            .get('/api/positions')
            .then(response => {
              this.positions = response.data;
              // Load nominees for each position
              this.positions.forEach(pos => {
                this.loadNomineesForPosition(pos.id);
              });
            });
        },
        methods: {
          updateResults() {
            axios
                .get('/api/results')
                .then(response => {
                    this.voterNumbers = response.data.voterNumbers,
                    this.position = response.data.position,
                    this.votersReceived = response.data.votersReceived,
                    this.rankersReceived = response.data.rankersReceived,
                    this.nomineesForRanking = response.data.nomineesForRanking
                });
          },
          voted(voter) {
              return this.votersReceived.indexOf(voter.toString()) != -1;
          },
          ranked(voter) {
              return this.rankersReceived.indexOf(voter.toString()) != -1;
          },
          updatePositionStatus(status) {
            axios
                .put('/api/positions/' + this.position.id, { status: status });
          },
          moveToPreviousStatus() {
            const statusOrder = ['vote', 'rank', 'results'];
            const currentIndex = statusOrder.indexOf(this.position.status);
            if (currentIndex > 0) {
              this.updatePositionStatus(statusOrder[currentIndex - 1]);
            }
          },
          moveToNextStatus() {
            const statusOrder = ['vote', 'rank', 'results'];
            const currentIndex = statusOrder.indexOf(this.position.status);
            if (currentIndex < statusOrder.length - 1) {
              this.updatePositionStatus(statusOrder[currentIndex + 1]);
            }
          },
          updatePositionVoteCount() {
            axios
                .put('/api/positions/' + this.position.id, { num_to_select: this.position.num_to_select});
          },
          updateDefaultPosition(new_position) {
            axios
                .put('/api/positions/default', { position_id: new_position });
          },
          confirmClearVotes() {
            this.showClearVotesModal = true;
          },
          clearVotes() {
            this.showClearVotesModal = false;
            axios
                .delete('/api/votes/' + this.position.id + '/' + this.clearVoterId);
	  },
          confirmClearRanks() {
            this.showClearRanksModal = true;
          },
          clearRanks() {
            this.showClearRanksModal = false;
            axios
                .delete('/api/ranks/' + this.position.id + '/' + this.clearRankerId);
	  },
          confirmImportNominees() {
            // Clear previous messages
            this.importMessage = '';
            this.importSuccess = false;
            this.importErrors = [];

            // Preview the add to get counts
            axios
                .post('/api/nominations/preview', {
                    position_id: this.managePositionId,
                    nominees_text: this.nomineesText
                })
                .then(response => {
                    this.importPreview = {
                        toImport: response.data.to_import,
                        duplicates: response.data.duplicates
                    };
                    this.showImportModal = true;
                })
                .catch(error => {
                    this.importSuccess = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        this.importMessage = error.response.data.message;
                    } else {
                        this.importMessage = 'An error occurred while previewing nominees.';
                    }
                    console.error('Preview error:', error);
                });
          },
          proceedWithImport() {
            // Close modal
            this.showImportModal = false;

            // Send add request
            axios
                .post('/api/nominations/import', {
                    position_id: this.managePositionId,
                    nominees_text: this.nomineesText
                })
                .then(response => {
                    this.importSuccess = true;
                    this.importMessage = response.data.message;
                    this.importErrors = response.data.errors || [];

                    // Clear the textarea on success if all added
                    if (response.data.added > 0 && response.data.skipped === 0) {
                        this.nomineesText = '';
                    }

                    // Reload nominees for the position
                    this.loadNomineesForPosition(this.managePositionId);
                })
                .catch(error => {
                    this.importSuccess = false;
                    if (error.response && error.response.data && error.response.data.message) {
                        this.importMessage = error.response.data.message;
                    } else {
                        this.importMessage = 'An error occurred while adding nominees.';
                    }
                    console.error('Add error:', error);
                });
          },
          loadNomineesForPosition(positionId) {
            axios
                .get(`/api/nominations/position/${positionId}`)
                .then(response => {
                    this.$set(this.nomineesByPosition, positionId, response.data);
                })
                .catch(error => {
                    console.error('Error loading nominees:', error);
                });
          },
          confirmRemoveNominee(positionId) {
            const nomineeId = this.removeNomineeId[positionId];
            if (!nomineeId) return;

            // Find the nominee details
            const nominee = this.nomineesByPosition[positionId].find(n => n.id == nomineeId);
            if (!nominee) return;

            this.nomineeToRemove = nomineeId;
            this.nomineeToRemoveName = `${nominee.last_name}, ${nominee.first_name}`;

            // Check if nominee has votes or ranks
            axios
                .get(`/api/nominations/${nomineeId}/has-votes-or-ranks`)
                .then(response => {
                    this.nomineeHasVotesOrRanks = response.data.has_votes_or_ranks;
                    this.showRemoveModal = true;
                })
                .catch(error => {
                    console.error('Error checking votes/ranks:', error);
                    // Show modal anyway
                    this.nomineeHasVotesOrRanks = false;
                    this.showRemoveModal = true;
                });
          },
          removeNominee() {
            if (!this.nomineeToRemove) return;

            axios
                .delete(`/api/nominations/${this.nomineeToRemove}`)
                .then(response => {
                    // Close modal
                    this.showRemoveModal = false;

                    // Reload nominees for all positions
                    this.positions.forEach(pos => {
                        this.loadNomineesForPosition(pos.id);
                    });

                    // Clear the selection
                    this.positions.forEach(pos => {
                        this.$set(this.removeNomineeId, pos.id, '');
                    });

                    // Refresh results in case this affected the ranking
                    this.updateResults();
                })
                .catch(error => {
                    console.error('Error removing nominee:', error);
                    alert('Failed to remove nominee. Please try again.');
                });
          },
        }
    }
</script>
