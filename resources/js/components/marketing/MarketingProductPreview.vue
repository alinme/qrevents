<script setup lang="ts">
import {
    CalendarDays,
    CreditCard,
    ImagePlus,
    LayoutDashboard,
    MessageSquare,
    MonitorPlay,
    QrCode,
    ScanLine,
    Sparkles,
    Tv,
    Upload,
    Users,
    Wallet,
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        variant:
            | 'hero'
            | 'event-setup'
            | 'guest-flow'
            | 'live-wall'
            | 'album-access'
            | 'business-dashboard'
            | 'business-billing'
            | 'event-create'
            | 'story-collage';
        aspectClass?: string;
        caption?: string;
    }>(),
    {
        aspectClass: 'aspect-[4/3]',
        caption: '',
    },
);
</script>

<template>
    <div class="marketing-preview" :class="aspectClass">
        <template v-if="variant === 'hero'">
            <div class="marketing-preview-orb marketing-preview-orb-left" />
            <div class="marketing-preview-orb marketing-preview-orb-right" />

            <div class="marketing-preview-grid">
                <div class="marketing-preview-card marketing-preview-wall-card">
                    <div class="marketing-preview-card-header">
                        <span class="marketing-preview-chip">Live wall</span>
                        <span class="marketing-preview-muted">eventsmart.app/wall/A7M2</span>
                    </div>
                    <div class="marketing-preview-wall-media">
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-main" />
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-side" />
                    </div>
                    <div class="marketing-preview-wall-footer">
                        <div>
                            <p class="marketing-preview-title">Wedding memories, already live</p>
                            <p class="marketing-preview-subtitle">Guests upload on phone. The wall updates in seconds.</p>
                        </div>
                        <Tv class="size-5 text-promo-primary" />
                    </div>
                </div>

                <div class="marketing-preview-phone">
                    <div class="marketing-preview-phone-notch" />
                    <div class="marketing-preview-phone-screen">
                        <div class="marketing-preview-card-header">
                            <span class="marketing-preview-chip">Guest upload</span>
                            <QrCode class="size-4 text-promo-primary" />
                        </div>
                        <div class="marketing-preview-upload-sheet">
                            <div class="marketing-preview-code-row">
                                <span>A</span>
                                <span>7</span>
                                <span>M</span>
                                <span>2</span>
                            </div>
                            <div class="marketing-preview-photo-stack">
                                <div class="marketing-preview-photo-thumb marketing-preview-photo-thumb-lg" />
                                <div class="marketing-preview-photo-thumb marketing-preview-photo-thumb-sm" />
                            </div>
                            <div class="marketing-preview-upload-actions">
                                <div class="marketing-preview-action-pill">
                                    <ScanLine class="size-4" />
                                    Scan
                                </div>
                                <div class="marketing-preview-action-pill">
                                    <Upload class="size-4" />
                                    Upload
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'event-setup'">
            <div class="marketing-preview-card h-full">
                <div class="marketing-preview-card-header">
                    <span class="marketing-preview-chip">Step 1</span>
                    <CalendarDays class="size-4 text-promo-primary" />
                </div>
                <div class="marketing-preview-list">
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Event</span>
                        <span class="marketing-preview-value">Ana & Luca</span>
                    </div>
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Plan</span>
                        <span class="marketing-preview-value">Plus</span>
                    </div>
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Album code</span>
                        <span class="marketing-preview-value">A7M2</span>
                    </div>
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Wall link</span>
                        <span class="marketing-preview-value">/wall/A7M2</span>
                    </div>
                </div>
                <div class="marketing-preview-cta-row">
                    <div class="marketing-preview-button-solid">Create event</div>
                    <div class="marketing-preview-button-ghost">Preview</div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'guest-flow'">
            <div class="marketing-preview-dual">
                <div class="marketing-preview-phone marketing-preview-phone-compact">
                    <div class="marketing-preview-phone-notch" />
                    <div class="marketing-preview-phone-screen">
                        <div class="marketing-preview-card-header">
                            <span class="marketing-preview-chip">Join fast</span>
                            <ScanLine class="size-4 text-promo-primary" />
                        </div>
                        <div class="marketing-preview-code-row marketing-preview-code-row-compact">
                            <span>A</span>
                            <span>7</span>
                            <span>M</span>
                            <span>2</span>
                        </div>
                        <div class="marketing-preview-mini-card">
                            <QrCode class="size-4 text-promo-primary" />
                            QR or short code
                        </div>
                    </div>
                </div>

                <div class="marketing-preview-phone marketing-preview-phone-compact">
                    <div class="marketing-preview-phone-notch" />
                    <div class="marketing-preview-phone-screen">
                        <div class="marketing-preview-card-header">
                            <span class="marketing-preview-chip">Post a memory</span>
                            <ImagePlus class="size-4 text-promo-primary" />
                        </div>
                        <div class="marketing-preview-photo-stack">
                            <div class="marketing-preview-photo-thumb marketing-preview-photo-thumb-lg" />
                        </div>
                        <div class="marketing-preview-mini-copy">
                            One photo, one message, done.
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'live-wall'">
            <div class="marketing-preview-card h-full">
                <div class="marketing-preview-card-header">
                    <span class="marketing-preview-chip">Live wall</span>
                    <MonitorPlay class="size-4 text-promo-primary" />
                </div>
                <div class="marketing-preview-tv-frame">
                    <div class="marketing-preview-wall-media">
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-main" />
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-side" />
                    </div>
                    <div class="marketing-preview-tv-bar">
                        <span>eventsmart.app/wall/A7M2</span>
                        <span>Live</span>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'album-access'">
            <div class="marketing-preview-phone h-full max-h-none w-full max-w-[20rem]">
                <div class="marketing-preview-phone-notch" />
                <div class="marketing-preview-phone-screen">
                    <div class="marketing-preview-card-header">
                        <span class="marketing-preview-chip">Album access</span>
                        <Users class="size-4 text-promo-primary" />
                    </div>
                    <p class="marketing-preview-title">Join the memory book</p>
                    <div class="marketing-preview-code-row">
                        <span>A</span>
                        <span>7</span>
                        <span>M</span>
                        <span>2</span>
                    </div>
                    <div class="marketing-preview-mini-copy">
                        Enter the code or scan the card at the venue.
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'business-dashboard'">
            <div class="marketing-preview-card h-full">
                <div class="marketing-preview-card-header">
                    <span class="marketing-preview-chip">Business</span>
                    <LayoutDashboard class="size-4 text-promo-primary" />
                </div>
                <div class="marketing-preview-dashboard-grid">
                    <div class="marketing-preview-stat">
                        <span class="marketing-preview-label">Credits</span>
                        <span class="marketing-preview-value">184</span>
                    </div>
                    <div class="marketing-preview-stat">
                        <span class="marketing-preview-label">Live events</span>
                        <span class="marketing-preview-value">6</span>
                    </div>
                    <div class="marketing-preview-dashboard-list">
                        <div class="marketing-preview-dashboard-item">
                            <span>Ana & Luca</span>
                            <span>Plus</span>
                        </div>
                        <div class="marketing-preview-dashboard-item">
                            <span>Launch party</span>
                            <span>Pro</span>
                        </div>
                        <div class="marketing-preview-dashboard-item">
                            <span>Rooftop dinner</span>
                            <span>Plus</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'business-billing'">
            <div class="marketing-preview-card h-full">
                <div class="marketing-preview-card-header">
                    <span class="marketing-preview-chip">Billing credits</span>
                    <Wallet class="size-4 text-promo-primary" />
                </div>
                <div class="marketing-preview-billing-balance">
                    <span class="marketing-preview-label">Available</span>
                    <strong>240 credits</strong>
                </div>
                <div class="marketing-preview-billing-list">
                    <div class="marketing-preview-dashboard-item">
                        <span>Top up</span>
                        <span>+100</span>
                    </div>
                    <div class="marketing-preview-dashboard-item">
                        <span>Wedding weekend</span>
                        <span>-35</span>
                    </div>
                    <div class="marketing-preview-dashboard-item">
                        <span>Client gala</span>
                        <span>-50</span>
                    </div>
                </div>
                <div class="marketing-preview-mini-card">
                    <CreditCard class="size-4 text-promo-primary" />
                    Top up once. Spend per event.
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'event-create'">
            <div class="marketing-preview-card h-full">
                <div class="marketing-preview-card-header">
                    <span class="marketing-preview-chip">Create event</span>
                    <Sparkles class="size-4 text-promo-primary" />
                </div>
                <div class="marketing-preview-list">
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Client</span>
                        <span class="marketing-preview-value">Studio Events</span>
                    </div>
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Plan cost</span>
                        <span class="marketing-preview-value">35 credits</span>
                    </div>
                    <div class="marketing-preview-row">
                        <span class="marketing-preview-label">Remaining</span>
                        <span class="marketing-preview-value">149 credits</span>
                    </div>
                </div>
                <div class="marketing-preview-cta-row">
                    <div class="marketing-preview-button-solid">Launch event</div>
                    <div class="marketing-preview-button-ghost">Save draft</div>
                </div>
            </div>
        </template>

        <template v-else-if="variant === 'story-collage'">
            <div class="marketing-preview-collage">
                <div class="marketing-preview-card marketing-preview-collage-card">
                    <div class="marketing-preview-card-header">
                        <span class="marketing-preview-chip">Album</span>
                        <QrCode class="size-4 text-promo-primary" />
                    </div>
                    <div class="marketing-preview-code-row marketing-preview-code-row-compact">
                        <span>A</span>
                        <span>7</span>
                        <span>M</span>
                        <span>2</span>
                    </div>
                </div>

                <div class="marketing-preview-card marketing-preview-collage-card">
                    <div class="marketing-preview-card-header">
                        <span class="marketing-preview-chip">Guest note</span>
                        <MessageSquare class="size-4 text-promo-primary" />
                    </div>
                    <p class="marketing-preview-subtitle">“Best dance floor ever.”</p>
                </div>

                <div class="marketing-preview-card marketing-preview-collage-card">
                    <div class="marketing-preview-card-header">
                        <span class="marketing-preview-chip">Wall</span>
                        <MonitorPlay class="size-4 text-promo-primary" />
                    </div>
                    <div class="marketing-preview-wall-media">
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-main" />
                        <div class="marketing-preview-wall-photo marketing-preview-wall-photo-side" />
                    </div>
                </div>
            </div>
        </template>

        <p v-if="caption" class="marketing-preview-caption">
            {{ caption }}
        </p>
    </div>
</template>
