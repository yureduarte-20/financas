

document.addEventListener('alpine:init', function () {
    Alpine.data('toast', (config) => ({
        type: config.type,
        duration: config.duration,
        position: config.position,
        timeout: null,
        notifications: [],
        open(config) {
            config.id = Date.now();
            config.message ??= '';
            config.type ??= this.type;
            config.duration ??= this.duration;
            config.position ??= this.position;

            this.notifications.push(config);
            if (config.duration > 0) {
                const t = setTimeout(() => {
                    this.close(config.id);
                    clearTimeout(t);
                }, config.duration);
            }
        },
        init() {

        },
        close(id) {
            this.notifications = this.notifications.filter(
                n => n.id !== id
            )

        },

        getBgClass(id) {
            const type = this.notifications.find((not) => not.id == id)?.type ?? this.type;
            const lightColors = {
                'info': 'bg-blue-500',
                'success': 'bg-teal-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500',
                'dark': 'bg-gray-800 dark:bg-neutral-900',
                'gray': 'bg-gray-500 dark:bg-neutral-700'
            };

            const darkColors = {
                'info': 'dark:bg-blue-700',
                'success': 'dark:bg-teal-700',
                'warning': 'dark:bg-yellow-700',
                'error': 'dark:bg-red-700',
                'dark': 'dark:bg-neutral-900',
                'gray': 'dark:bg-neutral-700'
            };

            // Retorna ambas as classes (light e dark)
            return `${lightColors[type] || 'bg-blue-500'} ${darkColors[type] || 'dark:bg-blue-700'}`;
        }
    }));
});

document.addEventListener('livewire:init', () => {
    Livewire.on('component::show-toast', ({ message, type }) => {
        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type, duration: 5000, position: 'right' } }));
    })
    Livewire.on('notify', ({ message, type }) => {
        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type, duration: 5000, position: 'right' } }));
    })
}, { once: true })

function initPreline() {
    window.HSStaticMethods.autoInit();
}
window.addEventListener('DOMContentLoaded', initPreline);
window.initPreline = initPreline;
