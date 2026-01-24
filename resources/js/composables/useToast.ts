import { ref, readonly } from 'vue';

export interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let idCounter = 0;

export function useToast() {
    const addToast = (toast: Omit<Toast, 'id'>) => {
        const id = `toast-${++idCounter}`;
        const newToast: Toast = {
            id,
            duration: 5000,
            ...toast,
        };

        toasts.value.push(newToast);

        if (newToast.duration && newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }

        return id;
    };

    const removeToast = (id: string) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (message: string, title?: string) => {
        return addToast({ type: 'success', message, title });
    };

    const error = (message: string, title?: string) => {
        return addToast({ type: 'error', message, title, duration: 7000 });
    };

    const warning = (message: string, title?: string) => {
        return addToast({ type: 'warning', message, title });
    };

    const info = (message: string, title?: string) => {
        return addToast({ type: 'info', message, title });
    };

    const clear = () => {
        toasts.value = [];
    };

    return {
        toasts: readonly(toasts),
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
        clear,
    };
}
