export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

// API Request/Response Types
export type LoginData = {
    email: string;
    password: string;
};

export type RegisterData = {
    email: string;
    password: string;
    name?: string;
};

export type AuthResponse = {
    user: User;
    token: string;
    message?: string;
};

export type ApiResponse<T = any> = {
    data?: T;
    message?: string;
    errors?: Record<string, string[]>;
};

// Dashboard Stats
export type DashboardStats = {
    landings: number;
    invitations: number;
    media: number;
};

// Media types
export type Media = {
    id: number;
    filename: string;
    path: string;
    url: string;
    mime_type: string;
    size: number;
    description?: string;
    user_id: number;
    created_at: string;
    updated_at: string;
};

// Theme types
export type Theme = {
    id: number;
    name: string;
    description?: string;
    primary_color: string;
    secondary_color: string;
    bg_color: string;
    bg_image_url?: string;
    bg_image_media_id?: number;
    css_class: string;
    is_system: boolean;
    user_id?: number;
    created_at: string;
    updated_at: string;
    // Relaciones
    background_image?: Media | null;
};
