export const parseVisitPath = (value: string | URL, origin: string): string => {
    try {
        return new URL(String(value), origin).pathname;
    } catch {
        return '/';
    }
};

export const shouldShowDefaultSuccessToast = (
    method: string,
    path: string,
): boolean => {
    if (method.toLowerCase() === 'get') {
        return false;
    }

    return !path.startsWith('/a/') && !path.startsWith('/wall/');
};
