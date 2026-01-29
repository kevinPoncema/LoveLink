<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
  date: string;
}>();

const now = ref(new Date());
let timer: number;

const diff = computed(() => {
  const start = new Date(props.date);
  const current = now.value;
  const difference = current.getTime() - start.getTime();

  if (difference < 0) return { years: 0, months: 0, days: 0, hours: 0, minutes: 0, seconds: 0 };

  // Calculate generic units (approximation for UI)
  const days = Math.floor(difference / (1000 * 60 * 60 * 24));
  const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((difference % (1000 * 60)) / 1000);

  // More precise year/month calculation could be done but days is usually preferred for "X days together"
  // Let's stick to Days + Time or Years/Months/Days
  
  const years = Math.floor(days / 365);
  const remainingDays = days % 365;
  const months = Math.floor(remainingDays / 30);
  
  return { years, months, days: remainingDays % 30, totalDays: days, hours, minutes, seconds };
});

onMounted(() => {
  timer = setInterval(() => {
    now.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <div class="flex gap-4 sm:gap-8 justify-center items-center text-center font-serif">
     <div v-if="diff.years > 0" class="flex flex-col">
      <span class="text-4xl sm:text-5xl font-bold">{{ diff.years }}</span>
      <span class="text-xs uppercase tracking-widest opacity-70">Años</span>
    </div>
    <div v-if="diff.years > 0 || diff.months > 0" class="flex flex-col">
      <span class="text-4xl sm:text-5xl font-bold">{{ diff.months }}</span>
      <span class="text-xs uppercase tracking-widest opacity-70">Meses</span>
    </div>
     <div class="flex flex-col">
      <span class="text-4xl sm:text-5xl font-bold">{{ diff.days }}</span>
      <span class="text-xs uppercase tracking-widest opacity-70">Días</span>
    </div>
     <div class="hidden sm:flex flex-col">
      <span class="text-4xl sm:text-5xl font-bold">{{ diff.hours }}</span>
      <span class="text-xs uppercase tracking-widest opacity-70">Horas</span>
    </div>
     <div class="hidden sm:flex flex-col">
      <span class="text-4xl sm:text-5xl font-bold">{{ diff.minutes }}</span>
      <span class="text-xs uppercase tracking-widest opacity-70">Min</span>
    </div>
     <div class="flex flex-col sm:hidden">
         <span class="text-xl font-bold">+</span>
     </div>
  </div>
</template>
