export const parseVisitPath = (value: string | URL, origin: string): string => {
    try {
        return new URL(String(value), origin).pathname;
    } catch {
        return '/';
    }
};

const authPaths = [
    '/login',
    '/logout',
    '/register',
    '/two-factor-challenge',
    '/forgot-password',
    '/reset-password',
    '/email/verification-notification',
    '/user/confirm-password',
];

export const shouldShowDefaultSuccessToast = (
    method: string,
    path: string,
): boolean => {
    if (method.toLowerCase() === 'get') {
        return false;
    }

    if (
        authPaths.some(
            (authPath) => path === authPath || path.startsWith(`${authPath}/`),
        )
    ) {
        return false;
    }

    return !path.startsWith('/a/') && !path.startsWith('/wall/');
};
