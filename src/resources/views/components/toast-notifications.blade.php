@props(['duration' => 5000])

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
        warning: { bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-800', icon: 'text-amber-600' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800', icon: 'text-blue-600' }
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
    >
      <!-- Success Style -->
      <template x-if="notification.type === 'success'">
        <div class="absolute inset-0 bg-success-50 border-success-200 rounded-xl -z-10"></div>
      </template>
      
      <!-- Error Style -->
      <template x-if="notification.type === 'error'">
        <div class="absolute inset-0 bg-primary-50 border-primary-200 rounded-xl -z-10"></div>
      </template>
      
      <!-- Warning Style -->
      <template x-if="notification.type === 'warning'">
        <div class="absolute inset-0 bg-amber-50 border-amber-200 rounded-xl -z-10"></div>
      </template>
      
      <!-- Info Style -->
      <template x-if="notification.type === 'info'">
        <div class="absolute inset-0 bg-blue-50 border-blue-200 rounded-xl -z-10"></div>
      </template>

      <!-- Icon -->
      <div class="flex-shrink-0 z-10">
        <template x-if="notification.type === 'success'">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-success-600" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </template>
        <template x-if="notification.type === 'error'">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary-600" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </template>
        <template x-if="notification.type === 'warning'">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </template>
        <template x-if="notification.type === 'info'">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        </template>
      </div>

      <!-- Message -->
      <p class="flex-1 text-sm font-medium pt-0.5 z-10" 
         :class="{
           'text-success-800': notification.type === 'success',
           'text-primary-800': notification.type === 'error',
           'text-amber-800': notification.type === 'warning',
           'text-blue-800': notification.type === 'info'
         }"
         x-text="notification.message"></p>

      <!-- Close Button -->
      <button 
        @click="remove(notification.id)"
        class="flex-shrink-0 hover:opacity-70 transition-opacity z-10"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </template>
</div>
