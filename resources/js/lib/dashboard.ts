import type { BusinessWalletActivity, RecentActivity, Tone } from '@/types';

type DateFormatOptions = {
    locale?: string;
    emptyLabel?: string;
};

type CreditFormatOptions = {
    creditsLabel?: string;
};

type TranslationResolver = (
    key: string,
    replacements?: Record<string, string | number>,
) => string;

type WalletCopyOptions = CreditFormatOptions & {
    t?: TranslationResolver;
};

const resolveIntlLocale = (locale?: string): string => {
    if (locale === 'ro') {
        return 'ro-RO';
    }

    if (locale === 'el') {
        return 'el-GR';
    }

    return 'en-GB';
};

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

export const formatDateOnly = (
    value: string | null,
    options: DateFormatOptions = {},
): string => {
    if (!value) {
        return options.emptyLabel ?? 'Date not set';
    }

    return new Intl.DateTimeFormat(resolveIntlLocale(options.locale), {
        dateStyle: 'long',
    }).format(new Date(value));
};

export const formatDateTime = (
    value: string | null,
    options: string | DateFormatOptions = 'No activity yet',
): string => {
    const resolvedOptions =
        typeof options === 'string' ? { emptyLabel: options } : options;

    if (!value) {
        return resolvedOptions.emptyLabel ?? 'No activity yet';
    }

    return new Intl.DateTimeFormat(resolveIntlLocale(resolvedOptions.locale), {
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

export const formatCreditDelta = (
    credits: number,
    options: CreditFormatOptions = {},
): string => {
    const absoluteCredits = Math.abs(credits);
    const creditsLabel = options.creditsLabel ?? 'credits';

    if (credits > 0) {
        return `+${absoluteCredits} ${creditsLabel}`;
    }

    if (credits < 0) {
        return `-${absoluteCredits} ${creditsLabel}`;
    }

    return `${absoluteCredits} ${creditsLabel}`;
};

export const walletActivityLabel = (
    item: BusinessWalletActivity,
    options: WalletCopyOptions = {},
): string => {
    const absoluteCredits = Math.abs(item.credits);
    const creditsLabel = options.creditsLabel ?? 'credits';

    if (item.kind === 'top_up') {
        return options.t
            ? options.t('dashboard.business.wallet.activity.top_up', {
                  count: absoluteCredits,
              })
            : `+${absoluteCredits} ${creditsLabel} added`;
    }

    if (item.kind === 'bonus') {
        return options.t
            ? options.t('dashboard.business.wallet.activity.bonus', {
                  count: absoluteCredits,
              })
            : `+${absoluteCredits} bonus ${creditsLabel}`;
    }

    if (item.kind === 'event_debit') {
        return options.t
            ? options.t('dashboard.business.wallet.activity.event_debit', {
                  count: absoluteCredits,
              })
            : `-${absoluteCredits} ${creditsLabel} used`;
    }

    return options.t
        ? options.t('dashboard.business.wallet.activity.adjustment', {
              count: formatCreditDelta(item.credits, { creditsLabel }),
          })
        : `${formatCreditDelta(item.credits, { creditsLabel })} updated`;
};

export const walletActivityKindLabel = (
    kind: BusinessWalletActivity['kind'],
    options: WalletCopyOptions = {},
): string => {
    if (options.t) {
        return options.t(`dashboard.business.wallet.kind.${kind}`);
    }

    switch (kind) {
        case 'top_up':
            return 'Top up';
        case 'bonus':
            return 'Bonus';
        case 'event_debit':
            return 'Event spend';
        default:
            return 'Adjustment';
    }
};

export const walletActivityTone = (
    kind: BusinessWalletActivity['kind'],
): Tone => {
    switch (kind) {
        case 'top_up':
        case 'bonus':
            return 'emerald';
        case 'event_debit':
            return 'amber';
        default:
            return 'zinc';
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
