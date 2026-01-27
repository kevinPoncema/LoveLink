import type { 
    User, 
    LoginData, 
    RegisterData, 
    AuthResponse,
    ApiResponse,
    DashboardStats
} from '@/types/auth';
import { apiClient } from '../ApiClient';
import { router } from '@inertiajs/vue3';

export class AuthService {
    /**
     * Autenticar usuario con email y contraseña usando Fortify
     */
    async login(credentials: LoginData): Promise<AuthResponse> {
        // Obtener CSRF token primero
        await apiClient.get('/sanctum/csrf-cookie');
        
        // Hacer login usando el endpoint de Fortify
        const response = await apiClient.post('/login', credentials);
        
        // Para Fortify con autenticación basada en sesiones, no hay token
        // El usuario quedará autenticado automáticamente por Laravel
        
        // Obtener datos del usuario después del login
        const user = await this.getUser();
        
        return {
            user,
            token: null, // No usamos tokens con Fortify sessions
        };
    }

    /**
     * Registrar nuevo usuario usando Fortify
     */
    async register(userData: RegisterData): Promise<AuthResponse> {
        // Obtener CSRF token primero
        await apiClient.get('/sanctum/csrf-cookie');
        
        // Hacer registro usando el endpoint de Fortify
        const response = await apiClient.post('/register', userData);
        
        // Para Fortify con autenticación basada en sesiones, no hay token
        // El usuario quedará autenticado automáticamente por Laravel
        
        // Obtener datos del usuario después del registro
        const user = await this.getUser();
        
        return {
            user,
            token: null, // No usamos tokens con Fortify sessions
        };
    }

    /**
     * Cerrar sesión del usuario usando Fortify
     */
    async logout(): Promise<void> {
        try {
            await apiClient.post('/logout');
        } finally {
            // Limpiar datos locales independientemente de la respuesta del servidor
            this.clearUser();
        }
    }

    /**
     * Obtener datos del usuario autenticado
     */
    async getUser(): Promise<User> {
        const response = await apiClient.get<User>('/user');
        
        if (response.data) {
            this.setUser(response.data);
            return response.data;
        }
        
        throw new Error('No se pudo obtener los datos del usuario');
    }

    /**
     * Verificar si el usuario está autenticado
     */
    isAuthenticated(): boolean {
        return !!this.getStoredUser();
    }

    /**
     * Obtener usuario almacenado en localStorage
     */
    getStoredUser(): User | null {
        try {
            const userString = localStorage.getItem('auth_user');
            return userString ? JSON.parse(userString) : null;
        } catch {
            return null;
        }
    }

    /**
     * Guardar usuario en localStorage
     */
    private setUser(user: User): void {
        localStorage.setItem('auth_user', JSON.stringify(user));
    }

    /**
     * Limpiar usuario de localStorage
     */
    private clearUser(): void {
        localStorage.removeItem('auth_user');
    }

    /**
     * Obtener estadísticas del dashboard (placeholder)
     * TODO: Esto deberá moverse a un servicio específico cuando se implemente
     */
    async getDashboardStats(): Promise<DashboardStats> {
        // Por ahora simularemos las estadísticas
        // Más adelante esto será una llamada real a la API
        return {
            landings: 0,
            invitations: 0,
            media: 0
        };
    }
}

// Instancia singleton del servicio
export const authService = new AuthService();