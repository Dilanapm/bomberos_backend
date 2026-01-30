@props(['id' => 'confirm-modal'])

<div 
  x-data="{ 
    show: false, 
    title: '', 
    message: '', 
    confirmText: 'Confirmar', 
    cancelText: 'Cancelar',
    icon: 'alert-triangle',
    iconColor: 'text-amber-600',
    iconBg: 'bg-amber-100',
    onConfirm: null,
    open(data) {
      this.title = data.title || 'Confirmar acción';
      this.message = data.message || '¿Estás seguro?';
      this.confirmText = data.confirmText || 'Confirmar';
      this.cancelText = data.cancelText || 'Cancelar';
      this.icon = data.icon || 'alert-triangle';
      this.iconColor = data.iconColor || 'text-amber-600';
      this.iconBg = data.iconBg || 'bg-amber-100';
      this.onConfirm = data.onConfirm;
      this.show = true;
    },
    confirm() {
      if (typeof this.onConfirm === 'function') {
        this.onConfirm();
      }
      this.show = false;
    },
    cancel() {
      this.show = false;
    }
  }"
  @confirm-modal.window="open($event.detail)"
  x-show="show"
  x-cloak
  class="fixed inset-0 z-50 overflow-y-auto"
  style="display: none;"
>
  <!-- Backdrop -->
  <div 
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-secondary-900 bg-opacity-75 transition-opacity"
    @click="cancel()"
  ></div>

  <!-- Modal -->
  <div class="flex min-h-screen items-center justify-center p-4">
    <div 
      x-show="show"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"
      @click.stop
    >
      <!-- Icon -->
      <div class="flex items-center justify-center mb-4">
        <div :class="iconBg" class="w-16 h-16 rounded-full flex items-center justify-center">
          <template x-if="icon === 'alert-triangle'">
            <x-lucide-alert-triangle :class="iconColor" class="w-8 h-8" />
          </template>
          <template x-if="icon === 'shield-off'">
            <x-lucide-shield-off :class="iconColor" class="w-8 h-8" />
          </template>
          <template x-if="icon === 'trash-2'">
            <x-lucide-trash-2 :class="iconColor" class="w-8 h-8" />
          </template>
          <template x-if="icon === 'ban'">
            <x-lucide-ban :class="iconColor" class="w-8 h-8" />
          </template>
          <template x-if="icon === 'alert-circle'">
            <x-lucide-alert-circle :class="iconColor" class="w-8 h-8" />
          </template>
        </div>
      </div>

      <!-- Title -->
      <h3 class="text-xl font-bold text-secondary-900 text-center mb-2" x-text="title"></h3>

      <!-- Message -->
      <p class="text-sm text-secondary-600 text-center mb-6 whitespace-pre-line" x-text="message"></p>

      <!-- Actions -->
      <div class="flex gap-3">
        <button
          @click="cancel()"
          class="flex-1 px-4 py-2.5 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 rounded-lg font-semibold transition-colors"
          x-text="cancelText"
        ></button>
        <button
          @click="confirm()"
          class="flex-1 px-4 py-2.5 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-semibold transition-colors"
          x-text="confirmText"
        ></button>
      </div>
    </div>
  </div>
</div>
