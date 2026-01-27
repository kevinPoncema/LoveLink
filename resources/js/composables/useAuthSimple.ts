import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { User } from '@/types/auth';

export function useAuth() {
    console.log('[DEBUG] useAuth composable inicializado');
    
    // Obtener datos de Inertia (compartidos desde HandleInertiaRequests)
    const page = usePage();
    
    console.log('[DEBUG] page.props:', page.props);
    console.log('[DEBUG] page.props.auth:', page.props.auth);
    
    // Usuario desde las props de Inertia (compartidas por HandleInertiaRequests)
    const user = computed<User | null>(() => {
        const authUser = page.props.auth?.user as User | null;
        console.log('[DEBUG] computed user:', authUser);
        return authUser || null;
    });

    // Estado de autenticación basado en si hay usuario
    const isAuthenticated = computed(() => {
        const authenticated = !!user.value;
        console.log('[DEBUG] isAuthenticated:', authenticated);
        return authenticated;
    });

    return {
        user,
        isAuthenticated,
    };
}