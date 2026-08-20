export type Role = {
    id: number;
    name: string;
    slug: string;
};

export type Department = {
    id: number;
    name: string;
    code: string;
};

export type User = {
    id: number;
    role_id?: number | null;
    department_id?: number | null;
    name: string;
    nip?: string | null;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    position?: string | null;
    phone?: string | null;
    status?: string;
    role?: Role | null;
    department?: Department | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
