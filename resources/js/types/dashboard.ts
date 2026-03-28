export type Tone =
    | 'dark'
    | 'emerald'
    | 'amber'
    | 'sky'
    | 'violet'
    | 'zinc'
    | 'rose';

export type Summary = {
    ownedEventCount: number;
    collaboratorEventCount: number;
    pendingSetupCount: number;
    totalUploadCount: number;
    pendingModerationCount: number;
    readyExportCount: number;
};

export type QuickAction = {
    label: string;
    description: string;
    url: string;
    tone: Tone;
};

export type BusinessOverview = {
    hasOwnedEvents: boolean;
    activeEventCount: number;
    liveEventCount: number;
    unpaidEventCount: number;
    overdueEventCount: number;
    readyExportCount: number;
    walletCredits: number;
    walletCurrency: string;
    totalAllocatedStorageBytes: number;
    totalUsedStorageBytes: number;
    totalFreeStorageBytes: number;
    storageUsagePercent: number;
};

export type BusinessWalletActivity = {
    id: number;
    kind: 'top_up' | 'bonus' | 'event_debit' | 'adjustment';
    credits: number;
    description: string;
    createdAt: string | null;
    eventName: string | null;
    eventUrl: string | null;
    moneyLabel: string | null;
};

export type BusinessWalletSummary = {
    currentBalance: number;
    currency: string;
    totalTransactions: number;
    creditsAdded: number;
    creditsUsed: number;
    latestActivityAt: string | null;
};

export type BusinessAttentionEvent = {
    id: number;
    name: string;
    plan: string;
    statusLabel: string;
    statusTone: Tone;
    billingLabel: string;
    billingTone: Tone;
    attentionLabel: string;
    attentionDetail: string;
    paymentDueAt: string | null;
    storageUsedBytes: number;
    storageLimitBytes: number;
    assetCount: number;
    links: {
        dashboard: string;
        media: string;
        settings: string;
        billing: string;
    };
};

export type DashboardEvent = {
    id: number;
    name: string;
    plan: string;
    eventDate: string | null;
    timezone: string;
    roleLabel: string;
    roleTone: Tone;
    statusLabel: string;
    statusTone: Tone;
    billingLabel: string;
    billingTone: Tone;
    mediaExportStatus: 'idle' | 'pending' | 'processing' | 'ready' | 'failed';
    mediaExportLabel: string;
    mediaExportTone: Tone;
    uploadCount: number;
    uploadLimit: number;
    storageUsedBytes: number;
    storageLimitBytes: number;
    guestCount: number;
    assetCount: number;
    approvedCount: number;
    processingCount: number;
    rejectedCount: number;
    lastUploadAt: string | null;
    paymentDueAt: string | null;
    isPaid: boolean;
    onboardingComplete: boolean;
    primaryAction: {
        label: string;
        url: string;
    };
    links: {
        dashboard: string;
        media: string;
        settings: string;
        billing: string;
        album: string;
        wall?: string;
        mediaExportDownload: string;
    };
    canManage: boolean;
};

export type RecentActivity = {
    id: number;
    eventName: string;
    eventUrl: string;
    activityUrl: string;
    kind: 'photo' | 'video' | 'text';
    guestName: string;
    summary: string;
    moderationStatus: 'approved' | 'rejected' | 'processing';
    createdAt: string | null;
};

export type DashboardLinks = {
    overview: string;
    business: string | null;
    createBusiness?: string | null;
    ownedEvents: string;
    recentActivity: string;
};

export type BusinessDashboardStatus =
    | 'all'
    | 'attention'
    | 'unpaid'
    | 'overdue'
    | 'live'
    | 'export_ready';

export type BusinessDashboardFilters = {
    search: string;
    status: BusinessDashboardStatus;
    selectionScope: 'none' | 'all_filtered';
    hasActiveFilters: boolean;
    ownedEventCount: number;
    ownedEventTotalCount: number;
    attentionCount: number;
    attentionTotalCount: number;
    statusOptions: Array<{
        value: BusinessDashboardStatus;
        label: string;
        count: number;
    }>;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginationMeta = {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    from: number | null;
    to: number | null;
    prevPageUrl: string | null;
    nextPageUrl: string | null;
    links: PaginationLink[];
};
