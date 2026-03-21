import MarketingController from './MarketingController'
import SocialAuthController from './SocialAuthController'
import DashboardController from './DashboardController'
import AdminController from './AdminController'
import EventOnboardingController from './EventOnboardingController'
import EventController from './EventController'
import StripeWebhookController from './StripeWebhookController'
import Settings from './Settings'

const Controllers = {
    MarketingController: Object.assign(MarketingController, MarketingController),
    SocialAuthController: Object.assign(SocialAuthController, SocialAuthController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    AdminController: Object.assign(AdminController, AdminController),
    EventOnboardingController: Object.assign(EventOnboardingController, EventOnboardingController),
    EventController: Object.assign(EventController, EventController),
    StripeWebhookController: Object.assign(StripeWebhookController, StripeWebhookController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers