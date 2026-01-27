// Exportar servicios principales
export { authService, AuthService } from './auth/AuthService';
export { landingService, LandingService } from './landing/LandingService';
export { invitationService, InvitationService } from './invitation/InvitationService';
export { mediaService } from './media/MediaService';
export { themeService, ThemeService } from './theme/ThemeService';

// Exportar el cliente API
export { apiClient } from './ApiClient';

// Exportar tipos principales
export type {
    // Auth types
    LoginData,
    RegisterData,
    AuthResponse,
    ApiResponse,
    DashboardStats
} from '@/types/auth';

export type {
    // Landing types
    Landing,
    CreateLandingData,
    UpdateLandingData
} from './landing/LandingService';

export type {
    // Invitation types
    Invitation,
    InvitationMedia,
    CreateInvitationData,
    UpdateInvitationData
} from './invitation/InvitationService';

export type {
    // Theme types
    Theme,
    CreateThemeData,
    UpdateThemeData,
    ThemeUploadResponse
} from './theme/ThemeService';