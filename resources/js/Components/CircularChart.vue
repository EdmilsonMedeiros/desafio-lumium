<template>
    <div class="flex flex-col items-center space-y-3 p-4 rounded-lg transition-all duration-300 hover:bg-gray-50">
        <div class="relative w-28 h-28">
            <svg class="w-28 h-28 transform -rotate-90" viewBox="0 0 36 36">
                <!-- Background circle -->
                <path
                    class="text-gray-200"
                    stroke="currentColor"
                    stroke-width="3"
                    fill="transparent"
                    d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <!-- Progress circle -->
                <path
                    :class="strokeColor"
                    stroke="currentColor"
                    stroke-width="3"
                    stroke-linecap="round"
                    fill="transparent"
                    :stroke-dasharray="`${circumference}`"
                    :stroke-dashoffset="animatedOffset"
                    class="transition-all duration-1000 ease-out"
                    d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                />
            </svg>
            <!-- Percentage text in center -->
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-lg font-bold" :class="textColor">{{ Math.round(animatedPercentage) }}%</span>
            </div>
        </div>
        <!-- Label -->
        <div class="text-center">
            <p class="text-sm font-semibold text-gray-700">{{ label }}</p>
            <p class="text-xs text-gray-500">{{ count.toLocaleString() }} logs</p>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';

const props = defineProps({
    percentage: {
        type: Number,
        default: 0
    },
    label: {
        type: String,
        required: true
    },
    count: {
        type: Number,
        default: 0
    },
    type: {
        type: String,
        default: 'safe', // safe, malicious, suspicious
        validator: (value) => ['safe', 'malicious', 'suspicious'].includes(value)
    }
});

const animatedPercentage = ref(0);
const circumference = computed(() => 2 * Math.PI * 15.9155);

const animatedOffset = computed(() => {
    return circumference.value - (animatedPercentage.value / 100) * circumference.value;
});

// Animação de entrada
const animateToPercentage = (targetPercentage) => {
    const duration = 1000; // 1 segundo
    const steps = 60;
    const stepSize = targetPercentage / steps;
    const stepDuration = duration / steps;
    
    let currentStep = 0;
    const timer = setInterval(() => {
        currentStep++;
        animatedPercentage.value = Math.min(stepSize * currentStep, targetPercentage);
        
        if (currentStep >= steps) {
            clearInterval(timer);
            animatedPercentage.value = targetPercentage;
        }
    }, stepDuration);
};

onMounted(() => {
    setTimeout(() => {
        animateToPercentage(props.percentage);
    }, 100);
});

watch(() => props.percentage, (newPercentage) => {
    animateToPercentage(newPercentage);
});

const strokeColor = computed(() => {
    switch (props.type) {
        case 'safe':
            return 'text-green-500';
        case 'malicious':
            return 'text-red-500';
        case 'suspicious':
            return 'text-yellow-500';
        default:
            return 'text-blue-500';
    }
});

const textColor = computed(() => {
    switch (props.type) {
        case 'safe':
            return 'text-green-600';
        case 'malicious':
            return 'text-red-600';
        case 'suspicious':
            return 'text-yellow-600';
        default:
            return 'text-blue-600';
    }
});
</script>
