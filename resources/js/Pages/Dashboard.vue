<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import TextInput from '@/Components/TextInput.vue';
import CircularChart from '@/Components/CircularChart.vue';
import { ref, onMounted, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import ModalShowDetails from '@/Components/ModalShowDetails.vue';

const dns_file = ref(null);
const logDNSFiles = ref([]);
const search = ref('');
const page = ref(1);
const logDNSStatistics = ref([]);
const pageProps = usePage();
const loading = ref(false);
const showModal = ref(false);
const logDNS = ref({});

// Estado das seções recolhíveis (recolhidas por padrão)
const isStatisticsExpanded = ref(false);
const isChartsExpanded = ref(false);

onMounted(() => {
    getLogDNSFiles(page.value, search.value);
    getLogDNSStatistics();
});

const getLogDNSStatistics = () => {
    axios.get('/log-dns/statistics').then(response => {
        logDNSStatistics.value = response.data;
        console.log(logDNSStatistics.value);
    });
}

const getLogDNSFiles = (page = 1, search = '') => {
    axios.post('/log-dns/get-log-dns', {
        page: page,
        search: search
    }).then(response => {
        logDNSFiles.value = response.data;
        // console.log(logDNSFiles.value);
    }).catch(error => {
        // console.log('Erro ao buscar arquivos');
    });

    getLogDNSStatistics();
}

const consultarDns = () => {
    loading.value = true;
    router.post(route('log-dns.submit'), {
        dns_file: dns_file.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            dns_file.value = null;
            getLogDNSFiles(page.value, search.value);
            loading.value = false;
        },
        onError: (error) => {
            loading.value = false;
        }
    });

    document.getElementById('dns_file').value = '';
}

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file && file.type === 'text/csv') {
        dns_file.value = file;
    } else {
        dns_file.value = null;
        if (file) {
            alert('Por favor, selecione apenas arquivos CSV (.csv)');
        }
    }
}

const updateTable = () => {
    page.value = 1;
    getLogDNSFiles(page.value, search.value);
}

const previousPage = () => {
    if(page.value > 1){
        page.value--;
    }
    getLogDNSFiles(page.value, search.value);
}

const nextPage = () => {
    if(page.value < logDNSFiles.value.last_page){
        page.value++;
    }
    getLogDNSFiles(page.value, search.value);
}

// Funções para alternar seções recolhíveis
const toggleStatistics = () => {
    isStatisticsExpanded.value = !isStatisticsExpanded.value;
};

const toggleCharts = () => {
    isChartsExpanded.value = !isChartsExpanded.value;
};

const chartData = computed(() => {
    if (!logDNSStatistics.value || !logDNSStatistics.value.totalLogs) {
        return {
            safe: { percentage: 0, count: 0 },
            malicious: { percentage: 0, count: 0 },
            suspicious: { percentage: 0, count: 0 }
        };
    }

    const total = logDNSStatistics.value.totalLogs;
    
    return {
        safe: {
            percentage: parseFloat(logDNSStatistics.value.percentualSeguro || 0),
            count: Math.round((parseFloat(logDNSStatistics.value.percentualSeguro || 0) / 100) * total)
        },
        malicious: {
            percentage: parseFloat(logDNSStatistics.value.percentualMalicioso || 0),
            count: Math.round((parseFloat(logDNSStatistics.value.percentualMalicioso || 0) / 100) * total)
        },
        suspicious: {
            percentage: parseFloat(logDNSStatistics.value.percentualSuspeito || 0),
            count: Math.round((parseFloat(logDNSStatistics.value.percentualSuspeito || 0) / 100) * total)
        }
    };
});

const showLogDetails = (log) => {
    showModal.value = true;
    logDNS.value = log;
};
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
    display: inline-block;
    width: 1em;
    height: 1em;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    vertical-align: -0.1em;
}
</style>

<template>
    <AppLayout title="Dashboard">
        <div class="py-12">
            <!-- Seção de Estatísticas Gerais -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header com botão para expandir/recolher -->
                        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-lg p-2 -m-2 transition-colors duration-200" @click="toggleStatistics">
                            <h3 class="text-lg font-semibold text-gray-900"> <i class="bi bi-graph-up"></i> Estatísticas Gerais</h3>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">
                                    {{ isStatisticsExpanded ? 'Recolher' : 'Expandir' }}
                                </span>
                                <svg 
                                    class="w-5 h-5 text-gray-400 transform transition-transform duration-200" 
                                    :class="{ 'rotate-180': isStatisticsExpanded }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Conteúdo recolhível -->
                        <div 
                            class="overflow-hidden transition-all duration-300 ease-in-out"
                            :class="isStatisticsExpanded ? 'max-h-96 opacity-100 mt-4' : 'max-h-0 opacity-0'"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Total de Logs -->
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">Total de Logs Analisados</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ logDNSStatistics.totalLogs || 0 }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Domínios Maliciosos Recentes -->
                                <div class="bg-red-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">Status de Segurança</p>
                                        </div>
                                    </div>
                                    <div v-if="logDNSStatistics.ultimos10Maliciosos && logDNSStatistics.ultimos10Maliciosos.length > 0">
                                        <p class="text-xs text-red-600 mb-2">{{ logDNSStatistics.totalMaliciosos }} domínios maliciosos detectados</p>
                                        <span v-for="(logDNS, index) in logDNSStatistics.ultimos10Maliciosos" :key="logDNS.id">
                                            {{ logDNS.dns }}{{ index >= 9 && logDNSStatistics.totalMaliciosos > 10 ? '...' : '; ' }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <div class="bg-green-100 border border-green-200 rounded-md p-2">
                                            <span class="text-xs text-green-700 font-medium">
                                                ✓ Nenhum domínio malicioso detectado
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Gráficos de Distribuição -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header com botão para expandir/recolher -->
                        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-lg p-2 -m-2 transition-colors duration-200" @click="toggleCharts">
                            <h3 class="text-lg font-semibold text-gray-900"> <i class="bi bi-graph-up"></i> Distribuição de Classificações</h3>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">
                                    {{ isChartsExpanded ? 'Recolher' : 'Expandir' }}
                                </span>
                                <svg 
                                    class="w-5 h-5 text-gray-400 transform transition-transform duration-200" 
                                    :class="{ 'rotate-180': isChartsExpanded }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Conteúdo recolhível -->
                        <div 
                            class="overflow-hidden transition-all duration-300 ease-in-out"
                            :class="isChartsExpanded ? 'max-h-96 opacity-100 mt-6' : 'max-h-0 opacity-0'"
                        >
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <CircularChart
                                    :percentage="chartData.safe.percentage"
                                    :count="chartData.safe.count"
                                    label="Seguros"
                                    type="safe"
                                />
                                <CircularChart
                                    :percentage="chartData.malicious.percentage"
                                    :count="chartData.malicious.count"
                                    label="Maliciosos"
                                    type="malicious"
                                />
                                <CircularChart
                                    :percentage="chartData.suspicious.percentage"
                                    :count="chartData.suspicious.count"
                                    label="Suspeitos"
                                    type="suspicious"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Importação de Arquivo -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex flex-col">
                        <div class="text-sm text-gray-500 gap-4 p-6 mb-0 pb-0 flex justify-between">
                            <div>
                                Importe um arquivo CSV com os dados de consulta.
                            </div>
                            <div v-if="pageProps.props.success.length > 0 || pageProps.props.error.length > 0" :class="['text-sm gap-4 mb-2 rounded-full p-2 font-semibold', pageProps.props.success.length > 0 ? 'text-green-600' : 'text-red-600']">
                                    {{ pageProps.props.success || pageProps.props.error }}
                                </div>
                        </div>
                        <div class="flex gap-4 p-4 pt-0 justify-between">
                            <div class="sm:w-full">

                                <input 
                                    type="file" 
                                    accept=".csv,text/csv" 
                                    @change="handleFileChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-full cursor-pointer"
                                    id="dns_file"
                                />
                            </div>
                            <PrimaryButton class="sm:w-1/4 justify-center self-end rounded-full gap-2" @click="consultarDns" :disabled="!dns_file || loading">
                                {{ loading ? 'Importando...' : 'Importar' }} <span v-if="loading" class="animate-spin">⚪</span> 
                                <i class="bi bi-upload"></i>
                            </PrimaryButton>
                        </div>

                        <div v-if="dns_file" class="text-sm text-green-600 gap-4 p-4 pt-0">
                            ✓ Arquivo selecionado: {{ dns_file.name }} ({{ dns_file.size }} KB)
                        </div>

                    </div>

                </div>
            </div>

            <!-- Seção de Histórico -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Histórico</h3>
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        placeholder="Pesquisar DNS, IP ou classificação..." 
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        v-model="search"
                                        @input="updateTable"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="bi bi-globe"></i>
                                            DNS
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="bi bi-globe"></i>
                                            Endereço IP
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="bi bi-clock"></i>
                                            Timestamp
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="bi bi-shield-check"></i>
                                            Classificação
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(logDNS, index) in logDNSFiles.data" class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" >
                                            {{ logDNS.dns ?? 'Não informado' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                            {{ logDNS.ip_address ?? 'Não informado' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">
                                            {{ logDNS.timestamp ?? 'Não informado' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', logDNS.classification == 'Seguro' ? 'bg-green-100 text-green-800' : (logDNS.classification == 'Suspeito' ? 'bg-yellow-100 text-yellow-800' : (logDNS.classification == 'Malicioso' ? 'bg-red-100 text-red-800' : 'text-gray-400' ))]">
                                                {{ logDNS.classification ?? 'Não classificado' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button @click="showLogDetails(logDNS)" class="text-blue-600 hover:text-blue-800">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button :class="['relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md bg-white hover:bg-gray-50', page == 1 ? 'opacity-90 text-gray-100 cursor-not-allowed' : 'text-gray-700']" @click="previousPage" :disabled="page == 1">
                                    Anterior
                                </button>
                                <button :class="['ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md  bg-white hover:bg-gray-50', page == logDNSFiles.last_page ? 'opacity-90 text-gray-100 cursor-not-allowed' : 'text-gray-700']" @click="nextPage" :disabled="page == logDNSFiles.last_page">
                                    Próximo
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p v-if="logDNSFiles.data" class="text-sm text-gray-700">
                                        Mostrando {{ logDNSFiles.data.length }} de {{ logDNSFiles.total }} resultados da página
                                        <span class="font-medium">{{ page }}</span>
                                        de
                                        <span class="font-medium">{{ logDNSFiles.last_page }}</span>
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginação">
                                        <!-- Botão Anterior -->
                                        <button class="relative inline-flex items-center px-4 py-3 rounded-l-md border border-gray-300 bg-white text-base font-medium text-gray-500 hover:bg-gray-50" @click="previousPage" :disabled="page == 1">
                                            <span class="sr-only">Anterior</span>
                                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        
                                        <!-- Botão Próximo -->
                                        <button class="relative inline-flex items-center px-4 py-3 rounded-r-md border border-gray-300 bg-white text-base font-medium text-gray-500 hover:bg-gray-50" @click="nextPage" :disabled="page == logDNSFiles.last_page">
                                            <span class="sr-only">Próximo</span>
                                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <ModalShowDetails 
        :show="showModal" 
        :logData="logDNS" 
        @close="showModal = false" 
    />

</template>

