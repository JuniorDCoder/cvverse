import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

export function setupInertiaToasts() {
    const toast = useToast();

    router.on('finish', (event) => {
        // @ts-ignore - Inertia event types are not fully typed
        const page = event.detail.page;

        if (!page || !page.props) return;

        // Handle validation errors
        if (page.props.errors && Object.keys(page.props.errors).length > 0) {
            const errors = page.props.errors as Record<string, string>;
            const errorMessages = Object.values(errors);

            // Show first error as toast (or combine multiple)
            if (errorMessages.length === 1) {
                toast.error(errorMessages[0], 'Validation Error');
            } else if (errorMessages.length > 1) {
                toast.error(
                    errorMessages.slice(0, 3).join(', ') + (errorMessages.length > 3 ? '...' : ''),
                    `${errorMessages.length} Validation Errors`
                );
            }
        }

        // Handle success flash messages
        if (page.props.flash?.success || (page.props as any).success) {
            const message = page.props.flash?.success || (page.props as any).success;
            if (message) {
                toast.success(message);
            }
        }

        // Handle error flash messages
        if (page.props.flash?.error || (page.props as any).error) {
            const message = page.props.flash?.error || (page.props as any).error;
            if (message) {
                toast.error(message);
            }
        }

        // Handle warning flash messages
        if (page.props.flash?.warning || (page.props as any).warning) {
            const message = page.props.flash?.warning || (page.props as any).warning;
            if (message) {
                toast.warning(message);
            }
        }

        // Handle info flash messages
        if (page.props.flash?.info || (page.props as any).info) {
            const message = page.props.flash?.info || (page.props as any).info;
            if (message) {
                toast.info(message);
            }
        }
    });
}
