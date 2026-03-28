export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    accountType: 'user' | 'business' | 'super_admin';
    accountLabel: string;
    isBusinessOnboarded: boolean;
    businessWalletCredits: number;
    capabilities: {
        accessAdmin: boolean;
        accessBusinessDashboard: boolean;
        canCreateMultipleEvents: boolean;
    };
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
