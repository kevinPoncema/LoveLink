import type { 
    User, 
    LoginData, 
    RegisterData, 
    AuthResponse,
    ApiResponse,
    DashboardStats
} from '@/types/auth';
import { apiClient } from '../ApiClient';

export class AuthService {
    /**
     * Autenticar usuario con email y contraseña
     */
    async login(credentials: LoginData): Promise<AuthResponse> {
        const response = await apiClient.post<AuthResponse>('/auth/login', credentials);
        
        if (response.data && response.data.token) {
            // Guardar token y usuario en localStorage
            apiClient.setAuthToken(response.data.token);
            this.setUser(response.data.user);
            return response.data;
        }
        
        throw new Error(response.message || 'Error en el login');
    }

    /**
     * Registrar nuevo usuario
     */
    async register(userData: RegisterData): Promise<AuthResponse> {
        const response = await apiClient.post<AuthResponse>('/auth/register', userData);
        
        if (response.data && response.data.token) {
            // Guardar token y usuario en localStorage
            apiClient.setAuthToken(response.data.token);
            this.setUser(response.data.user);
            return response.data;
        }
        
        throw new Error(response.message || 'Error en el registro');
    }

    /**
     * Cerrar sesión del usuario
     */
    async logout(): Promise<void> {
        try {
            await apiClient.post('/auth/logout');
        } finally {
            // Limpiar datos locales independientemente de la respuesta del servidor
            apiClient.clearAuthToken();
            this.clearUser();
        }
    }

    /**
     * Obtener datos del usuario autenticado
     */
    async getUser(): Promise<User> {
        const response = await apiClient.get<User>('/auth/user');
        
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
        return !!apiClient.getAuthToken() && !!this.getStoredUser();
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