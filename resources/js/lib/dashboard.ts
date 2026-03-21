import type { RecentActivity, Tone } from '@/types';

export const formatBytes = (bytes: number): string => {
    if (!Number.isFinite(bytes) || bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** exponent;

    return `${value >= 10 || exponent === 0 ? value.toFixed(0) : value.toFixed(1)} ${units[exponent]}`;
};

export const formatDateOnly = (value: string | null): string => {
    if (!value) {
        return 'Date not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'long',
    }).format(new Date(value));
};

export const formatDateTime = (
    value: string | null,
    emptyLabel = 'No activity yet',
): string => {
    if (!value) {
        return emptyLabel;
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

export const badgeClass = (tone: Tone): string => {
    switch (tone) {
        case 'dark':
            return 'bg-[#171411] text-white';
        case 'emerald':
            return 'bg-emerald-100 text-emerald-800';
        case 'amber':
            return 'bg-amber-100 text-amber-800';
        case 'sky':
            return 'bg-sky-100 text-sky-800';
        case 'violet':
            return 'bg-violet-100 text-violet-800';
        case 'rose':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-zinc-100 text-zinc-700';
    }
};

export const moderationBadgeClass = (
    status: RecentActivity['moderationStatus'],
): string => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-800';
        case 'rejected':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-amber-100 text-amber-800';
    }
};
