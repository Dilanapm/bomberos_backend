@props(['duration' => 8000])

<div 
  x-data="{ 
    notifications: [],
    add(data) {
      const id = Date.now();
      this.notifications.push({
        id,
        type: data.type || 'success',
        message: data.message,
        icon: this.getIcon(data.type || 'success'),
      });
      
      setTimeout(() => {
        this.remove(id);
      }, {{ $duration }});
    },
    remove(id) {
      this.notifications = this.notifications.filter(n => n.id !== id);
    },
    getIcon(type) {
      const icons = {
        success: 'check-circle',
        error: 'x-circle',
        warning: 'alert-triangle',
        info: 'info'
      };
      return icons[type] || 'info';
    },
    getColors(type) {
      const colors = {
        success: { bg: 'bg-success-50', border: 'border-success-200', text: 'text-success-800', icon: 'text-success-600' },
        error: { bg: 'bg-primary-50', border: 'border-primary-200', text: 'text-primary-800', icon: 'text-primary-600' },
        warning: { bg: 'bg-accent-200', border: 'border-accent-400', text: 'text-accent-500', icon: 'text-accent-500' },
        info: { bg: 'bg-secondary-50', border: 'border-info-500', text: 'text-info-600', icon: 'text-info-500' }
      };
      return colors[type] || colors.info;
    }
  }"
  @notify.window="add($event.detail)"
  class="fixed top-4 right-4 z-50 space-y-3"
>
  <template x-for="notification in notifications" :key="notification.id">
    <div
      x-show="true"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-x-full"
      x-transition:enter-end="opacity-100 translate-x-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-x-0"
      x-transition:leave-end="opacity-0 translate-x-full"
      class="relative flex items-start gap-3 p-4 rounded-xl shadow-lg border-2 max-w-md min-w-80"
      :class="{
        'bg-success-200 dark:bg-dark-1 border-success-400 dark:border-success-400': notification.type === 'success',
        'bg-primary-50 dark:bg-dark-1 border-primary-200 dark:border-primary-600': notification.type === 'error',
        'bg-accent-200 dark:dark:bg-dark-1 border-accent-400 dark:border-amber-600': notification.type === 'warning',
        'bg-info-300 dark:bg-dark-1 border-info-500 dark:border-info-500': notification.type === 'info'
      }"
    >

      <!-- Icon -->
      <div class="flex-shrink-0 z-10">
        <template x-if="notification.type === 'success'">
          <div class="text-success-600 dark:text-success-400">
            <x-lucide-check-circle class="w-6 h-6" />
          </div>
        </template>
        <template x-if="notification.type === 'error'">
          <div class="text-dark-10 dark:text-dark-9">
            <x-lucide-x-circle class="w-6 h-6" />
          </div>
        </template>
        <template x-if="notification.type === 'warning'">
          <div class="text-accent-500 dark:text-accent-400">
            <x-lucide-alert-triangle class="w-6 h-6" />
          </div>
        </template>
        <template x-if="notification.type === 'info'">
          <div class="text-info-500 dark:text-info-400">
            <x-lucide-info class="w-6 h-6" />
          </div>
        </template>
      </div>

      <!-- Message -->
      <p class="flex-1 text-sm font-bold pt-0.5 z-10" 
         :class="{
           'text-success-600 dark:text-success-400': notification.type === 'success',
           'text-dark-10 dark:text-dark-10': notification.type === 'error',
           'text-accent-500 dark:text-accent-500': notification.type === 'warning',
           'text-info-600 dark:text-info-400': notification.type === 'info'
         }"
         x-text="notification.message"></p>

      <!-- Close Button -->
      <button 
        @click="remove(notification.id)"
        class="flex-shrink-0 text-secondary-400 dark:text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 transition-colors z-10"
      >
        <x-lucide-x class="w-5 h-5" />
      </button>
    </div>
  </template>
</div>
