const ensureHeadLink = (id: string, href: string, rel: string, crossOrigin?: 'anonymous'): HTMLLinkElement => {
    const existing = document.head.querySelector<HTMLLinkElement>(`#${id}`);

    if (existing) {
        existing.href = href;
        existing.rel = rel;

        if (crossOrigin) {
            existing.crossOrigin = crossOrigin;
        } else {
            existing.removeAttribute('crossorigin');
        }

        return existing;
    }

    const link = document.createElement('link');
    link.id = id;
    link.rel = rel;
    link.href = href;

    if (crossOrigin) {
        link.crossOrigin = crossOrigin;
    }

    document.head.append(link);

    return link;
};

export const syncGoogleFontStylesheet = (href: string, prefix: string): void => {
    ensureHeadLink(`${prefix}-fonts-preconnect`, 'https://fonts.googleapis.com', 'preconnect');
    ensureHeadLink(`${prefix}-static-preconnect`, 'https://fonts.gstatic.com', 'preconnect', 'anonymous');
    ensureHeadLink(`${prefix}-stylesheet`, href, 'stylesheet');
};

export const removeGoogleFontStylesheet = (prefix: string): void => {
    document.head.querySelector(`#${prefix}-stylesheet`)?.remove();
    document.head.querySelector(`#${prefix}-fonts-preconnect`)?.remove();
    document.head.querySelector(`#${prefix}-static-preconnect`)?.remove();
};
