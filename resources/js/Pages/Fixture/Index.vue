<script setup>
import {Head, router, Link} from '@inertiajs/vue3';
import {onMounted, ref, watch} from "vue";

const props = defineProps({
    week: {
        type: Number
    },
    minWeek: {
        type: Number
    },
    maxWeek: {
        type: Number
    },
    fixture: {
        type: Array
    },
    playerStats: {
        type: Array,
        default: [],
    }
})

const isSimulating = ref(false);
const isAllGamesPlayed = ref(false);

const top10Scorer = ref([]);
const top10Assists = ref([]);

const simulateGames = () => {
    router.post(route('fixture.simulate', {week: props.week}))
    isSimulating.value = true;
}

const calculateTop15Score = (data) => {
    // Calculate total points for each stat object
    data.forEach((stats) => {
        stats.total_points = (stats.two_point_success * 2) + (stats.three_point_success * 3);
    });

    // Sort the data by total points in descending order
    data.sort((a, b) => b.total_points - a.total_points);

    // Select the top 15 scorers
    top10Scorer.value = data.slice(0, 15);
};

const calculateTop15Assist = (data) => {
    // Sort the data by assist count in descending order
    data.sort((a, b) => b.assist_count - a.assist_count);

    // Select the top 15 scorers
    top10Assists.value = data.slice(0, 15);
}

const checkAllGamesPlayed = () => {
    isAllGamesPlayed.value = props.fixture.every(game => game.status === 3);
}

onMounted(function () {
    checkAllGamesPlayed();
    calculateTop15Score(props.playerStats)
    calculateTop15Assist(props.playerStats)

    Echo.channel('game-started')
        .listen('.GameStartedEvent', (e) => {
            const gameToUpdate = props.fixture.find((game) => game.id === e.game.id);

            if (gameToUpdate) {
                gameToUpdate.status = e.game.status

                calculateTop15Score(props.playerStats);
                calculateTop15Assist(props.playerStats);
            }
        });

    Echo.channel('game-ended')
        .listen('.GameEndedEvent', (e) => {
            const gameToUpdate = props.fixture.find((game) => game.id === e.game.id);

            if (gameToUpdate) {
                gameToUpdate.status = e.game.status
            }

            checkAllGamesPlayed();
        });

    Echo.channel('new-attack')
        .listen('.NewAttackEvent', (e) => {
            const gameToUpdate = props.fixture.find((game) => game.id === e.game.id);

            if (gameToUpdate) {
                gameToUpdate.stats = e.game.stats;
            }
        });

    Echo.channel('new-score')
        .listen('.NewScoreEvent', (e) => {
            const gameToUpdate = props.fixture.find((game) => game.id === e.game.id);

            if (gameToUpdate) {
                gameToUpdate.stats = e.game.stats

                calculateTop15Score(props.playerStats);
                calculateTop15Assist(props.playerStats);
            }
        });

    Echo.channel('new-point')
        .listen('.NewPointEvent', (e) => {
            let playerToUpdate = props.playerStats.find((stats) => stats.id === e.player.id);
            let assistedPlayerToUpdate = props.playerStats.find((stats) => stats.id === e.assistedBy.id);

            if (playerToUpdate) {
                playerToUpdate.assist_count = e.player.assist_count;
                playerToUpdate.two_point_attempt = e.player.two_point_attempt;
                playerToUpdate.two_point_success = e.player.two_point_success;
                playerToUpdate.three_point_attempt = e.player.three_point_attempt;
                playerToUpdate.three_point_success = e.player.three_point_success;
            } else {
                props.playerStats.push(e.player);
            }

            if (assistedPlayerToUpdate) {
                assistedPlayerToUpdate.assist_count = e.assistedBy.assist_count;
                assistedPlayerToUpdate.two_point_attempt = e.assistedBy.two_point_attempt;
                assistedPlayerToUpdate.two_point_success = e.assistedBy.two_point_success;
                assistedPlayerToUpdate.three_point_attempt = e.assistedBy.three_point_attempt;
                assistedPlayerToUpdate.three_point_success = e.assistedBy.three_point_success;
            } else {
                props.playerStats.push(e.assistedBy);
            }

            calculateTop15Score(props.playerStats);
            calculateTop15Assist(props.playerStats);
        });
})
</script>

<template>
    <Head title="Fixture"/>

    <div class="p-4">
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-3 bg-gray-50 rounded-xl p-2">
                <div class="bg-gray-900 text-center text-white -m-2 rounded-t-xl mb-1">
                    <span class="text-md font-bold">Top 15 Scorers of Week</span>
                </div>

                <div v-for="stat in top10Scorer" class="flex flex-col my-4 text-xs">
                    <div class="flex justify-between">
                        <span class="p-1">
                            {{ stat.player.name }} {{ stat.player.surname }} ({{ stat.player.team.short_name }})
                        </span>
                        <span class="p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                            {{ stat.total_points }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-span-6 bg-gray-50 rounded-xl p-2">
                <div class="bg-gray-900 text-center text-white -m-2 rounded-t-xl mb-1">
                    <span class="text-md font-bold">{{ week }}. Week</span>
                </div>
                <div v-for="game in fixture" class="flex flex-col my-4 text-xs">
                    <div class="flex justify-between">
                        <div class="flex flex-1 justify-end items-center gap-1">
                            <div
                                class="flex items-center justify-center gap-1 p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M19 1.48416e-05L23 0C23.2652 -9.53668e-07 23.5195 0.105355 23.7071 0.292891C23.8946 0.480426 24 0.73478 24 0.999997L24 5.00001C24 5.26523 23.8946 5.51958 23.7071 5.70712L11.9142 17.5L13.7071 19.2929C14.0976 19.6834 14.0976 20.3166 13.7071 20.7071C13.3166 21.0977 12.6834 21.0977 12.2929 20.7071L9.79289 18.2071L9.46376 17.878L5.9999 20.9955C6.00096 21.7635 5.70873 22.534 5.12132 23.1214C3.94975 24.293 2.05025 24.293 0.87868 23.1214C-0.292893 21.9498 -0.292893 20.0503 0.87868 18.8787C1.46607 18.2913 2.23647 17.9991 3.00451 18.0002L6.12202 14.5363L5.79287 14.2071L3.29289 11.7071C2.90237 11.3166 2.90237 10.6834 3.29289 10.2929C3.68342 9.90239 4.31658 9.90239 4.70711 10.2929L6.49998 12.0858L18.2929 0.292907C18.4804 0.105372 18.7348 1.57952e-05 19 1.48416e-05ZM7.91419 13.5L8.2071 13.7929L10.2071 15.7929L10.5 16.0858L22 4.5858L22 2L19.4142 2.00001L7.91419 13.5ZM7.53819 15.9524L5.00435 18.7678C5.0441 18.8035 5.08311 18.8405 5.12132 18.8787C5.15952 18.9169 5.19648 18.9559 5.23221 18.9957L8.04759 16.4618L7.53819 15.9524ZM3.20676 20.0214C2.88445 19.954 2.54009 20.0458 2.29289 20.293C1.90237 20.6835 1.90237 21.3166 2.29289 21.7072C2.68342 22.0977 3.31658 22.0977 3.70711 21.7072C3.95431 21.46 4.0461 21.1156 3.97862 20.7933C3.94032 20.6103 3.85075 20.4366 3.70711 20.293C3.56346 20.1493 3.3897 20.0597 3.20676 20.0214Z"
                                          fill="#000000"/>
                                </svg>

                                {{ game.stats ? game.stats.home_attack : '0' }}
                            </div>
                            {{ game.home_team.name }} ({{ game.home_team.short_name }})
                        </div>

                        <div class="flex space-x-2 w-32 justify-center items-center">
                            <span class="p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                                {{ game.stats ? game.stats.home_score : '0' }}
                            </span>

                            <span
                                :class="{
                                'bg-gray-600' : game.status === 1,
                                'bg-emerald-600 animate-pulse': game.status === 2,
                                'bg-rose-600' : game.status === 3,
                                }"
                                class="size-2 rounded-full"></span>

                            <span class="p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                                {{ game.stats ? game.stats.away_score : '0' }}
                            </span>
                        </div>

                        <div class="flex flex-1 justify-start items-center gap-1">
                            ({{ game.away_team.short_name }}) {{ game.away_team.name }}

                            <div
                                class="flex items-center justify-center gap-1 p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M19 1.48416e-05L23 0C23.2652 -9.53668e-07 23.5195 0.105355 23.7071 0.292891C23.8946 0.480426 24 0.73478 24 0.999997L24 5.00001C24 5.26523 23.8946 5.51958 23.7071 5.70712L11.9142 17.5L13.7071 19.2929C14.0976 19.6834 14.0976 20.3166 13.7071 20.7071C13.3166 21.0977 12.6834 21.0977 12.2929 20.7071L9.79289 18.2071L9.46376 17.878L5.9999 20.9955C6.00096 21.7635 5.70873 22.534 5.12132 23.1214C3.94975 24.293 2.05025 24.293 0.87868 23.1214C-0.292893 21.9498 -0.292893 20.0503 0.87868 18.8787C1.46607 18.2913 2.23647 17.9991 3.00451 18.0002L6.12202 14.5363L5.79287 14.2071L3.29289 11.7071C2.90237 11.3166 2.90237 10.6834 3.29289 10.2929C3.68342 9.90239 4.31658 9.90239 4.70711 10.2929L6.49998 12.0858L18.2929 0.292907C18.4804 0.105372 18.7348 1.57952e-05 19 1.48416e-05ZM7.91419 13.5L8.2071 13.7929L10.2071 15.7929L10.5 16.0858L22 4.5858L22 2L19.4142 2.00001L7.91419 13.5ZM7.53819 15.9524L5.00435 18.7678C5.0441 18.8035 5.08311 18.8405 5.12132 18.8787C5.15952 18.9169 5.19648 18.9559 5.23221 18.9957L8.04759 16.4618L7.53819 15.9524ZM3.20676 20.0214C2.88445 19.954 2.54009 20.0458 2.29289 20.293C1.90237 20.6835 1.90237 21.3166 2.29289 21.7072C2.68342 22.0977 3.31658 22.0977 3.70711 21.7072C3.95431 21.46 4.0461 21.1156 3.97862 20.7933C3.94032 20.6103 3.85075 20.4366 3.70711 20.293C3.56346 20.1493 3.3897 20.0597 3.20676 20.0214Z"
                                          fill="#000000"/>
                                </svg>

                                {{ game.stats ? game.stats.away_attack : '0' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between gap-2 px-2 pt-4">
                    <Link v-if="week !== minWeek" :href="route('fixture.index', {week: week - 1})"
                          class="px-4 py-2 bg-emerald-600 text-white rounded-xl w-full text-center">Previous Week</Link>

                    <button v-if="!isAllGamesPlayed" @click="simulateGames" :disabled="isSimulating"
                            :class="{'bg-gray-600' : isSimulating}"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-xl w-full">
                        Simulate Games
                    </button>

                    <Link v-if="week !== maxWeek" :href="route('fixture.index', {week: week + 1})"
                          class="px-4 py-2 bg-emerald-600 text-white rounded-xl w-full text-center">Next Week</Link>
                </div>
            </div>

            <div class="col-span-3 bg-gray-50 rounded-xl p-2">
                <div class="bg-gray-900 text-center text-white -m-2 rounded-t-xl mb-1">
                    <span class="text-md font-bold">Top 15 Assists of Week</span>
                </div>

                <div v-for="stat in top10Assists" class="flex flex-col my-4 text-xs">
                    <div class="flex justify-between">
                        <span class="p-1">
                            {{ stat.player.name }} {{ stat.player.surname }} ({{ stat.player.team.short_name }})
                        </span>
                        <span class="p-1 bg-gray-200 text-black rounded-md w-12 text-center">
                            {{ stat.assist_count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <div class="bg-gray-900 text-center text-white p-2 rounded-t-xl mt-2">
                <span class="text-md font-bold">Player Stats of Week</span>
            </div>
            <table class="w-full">
                <thead>
                <tr class="text-xs font-semibold tracking-wide text-left text-gray-900 bg-gray-100 uppercase border-b border-gray-600">
                    <th class="px-4 py-3">Player</th>
                    <th class="px-4 py-3">Assist</th>
                    <th class="px-4 py-3">2pts Attempt</th>
                    <th class="px-4 py-3">2pts Rate</th>
                    <th class="px-4 py-3">3pts Attempt</th>
                    <th class="px-4 py-3">3pts Rate</th>
                    <th class="px-4 py-3">Total Pts</th>
                </tr>
                </thead>
                <tbody class="bg-white">
                <tr v-for="playerStat in playerStats" class="text-gray-700">
                    <td class="px-4 py-3 border">
                        <div class="flex items-center text-sm">
                            <div>
                                <p class="font-semibold text-black">
                                    {{ playerStat.player.name }} {{ playerStat.player.surname }}
                                </p>
                                <p class="text-xs text-gray-600">{{ playerStat.player.team.name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-ms font-semibold border">{{ playerStat.assist_count }}</td>
                    <td class="px-4 py-3 text-ms font-semibold border">{{ playerStat.two_point_attempt }}</td>
                    <td class="px-4 py-3 text-ms font-semibold border">
                        {{
                            playerStat.two_point_attempt ? Math.round((playerStat.two_point_success / playerStat.two_point_attempt) * 100) : 0
                        }}%
                    </td>
                    <td class="px-4 py-3 text-ms font-semibold border">{{ playerStat.three_point_attempt }}</td>
                    <td class="px-4 py-3 text-ms font-semibold border">
                        {{
                            playerStat.three_point_attempt ? Math.round((playerStat.three_point_success / playerStat.three_point_attempt) * 100) : 0
                        }}%
                    </td>
                    <td class="px-4 py-3 text-ms font-semibold border">
                        {{ (playerStat.two_point_success * 2) + (playerStat.three_point_success * 3) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
