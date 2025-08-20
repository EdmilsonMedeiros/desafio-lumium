<template>
    <Modal :show="show" @close="closeModal" closeable max-width="2xl">
        <div class="px-6 py-4">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                    📊 Detalhes do Log DNS
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="py-6 space-y-6">
                <!-- Informações Básicas -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        🌐 Informações Básicas
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">DNS</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ logData.dns || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ logData.ip_address || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.timestamp) }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Classificação</label>
                                <div class="mt-1">
                                    <span :class="getClassificationClass(logData.classification)" class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium">
                                        {{ getClassificationIcon(logData.classification) }} {{ logData.classification || 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data da consulta</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dados WHOIS -->
                <div v-if="hasWhoisData" class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        🌍 Informações WHOIS
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div v-if="logData.country_name">
                                <label class="block text-sm font-medium text-gray-700">País</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ logData.country_name }}</p>
                            </div>
                            <div v-if="logData.state">
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ logData.state }}</p>
                            </div>
                            <div v-if="logData.city">
                                <label class="block text-sm font-medium text-gray-700">Cidade</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ logData.city }}</p>
                            </div>
                            <div v-if="logData.company">
                                <label class="block text-sm font-medium text-gray-700">Empresa</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ logData.company }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div v-if="logData.create_date">
                                <label class="block text-sm font-medium text-gray-700">Data de Criação</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.create_date) }}</p>
                            </div>
                            <div v-if="logData.update_date">
                                <label class="block text-sm font-medium text-gray-700">Data de Atualização</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.update_date) }}</p>
                            </div>
                            <div v-if="logData.expiry_date">
                                <label class="block text-sm font-medium text-gray-700">Data de Expiração</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.expiry_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ logData.status == 1 ? 'Ativo' : 'Inativo' }}</p>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Resposta da IA -->
                <div v-if="logData.ai_response" class="bg-purple-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        🤖 Análise da IA
                    </h4>
                    <div class="bg-white p-4 rounded border">
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono">{{ logData.ai_response }}</pre>
                    </div>
                </div>

                <!-- Informações do Arquivo -->
                <div v-if="logData.log_dns_file" class="bg-green-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        📁 Informações do Arquivo
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome do Arquivo</label>
                            <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border font-mono">{{ logData.log_dns_file.file_name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de Upload</label>
                            <p class="mt-1 text-sm text-gray-900 bg-white px-3 py-2 rounded border">{{ formatDate(logData.log_dns_file.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from './Modal.vue';
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    logData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Computed para verificar se tem dados WHOIS
const hasWhoisData = computed(() => {
    return props.logData.country_name || 
           props.logData.state || 
           props.logData.city || 
           props.logData.company || 
           props.logData.create_date || 
           props.logData.update_date || 
           props.logData.expiry_date;
});

// Função para formatar datas
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    } catch {
        return dateString;
    }
};

// Função para obter classe CSS da classificação
const getClassificationClass = (classification) => {
    const classes = {
        'Seguro': 'bg-green-100 text-green-800',
        'Suspeito': 'bg-yellow-100 text-yellow-800',
        'Malicioso': 'bg-red-100 text-red-800'
    };
    return classes[classification] || 'bg-gray-100 text-gray-800';
};

// Função para obter ícone da classificação
const getClassificationIcon = (classification) => {
    const icons = {
        'Seguro': '✅',
        'Suspeito': '⚠️',
        'Malicioso': '🚨'
    };
    return icons[classification] || '❓';
};
</script>