export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role: 'user' | 'admin';
    job_title?: string;
    industry?: string;
    experience_level?: string;
    interests?: string[];
    phone?: string;
    location?: string;
    bio?: string;
    onboarding_completed: boolean;
    onboarding_completed_at?: string;
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
