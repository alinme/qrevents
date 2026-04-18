import MarketingController from './MarketingController'
import SocialAuthController from './SocialAuthController'
import RegistrationController from './RegistrationController'
import EventOnboardingController from './EventOnboardingController'
import DashboardController from './DashboardController'
import BusinessController from './BusinessController'
import AdminController from './AdminController'
import EventController from './EventController'
import StripeWebhookController from './StripeWebhookController'
import Settings from './Settings'

const Controllers = {
    MarketingController: Object.assign(MarketingController, MarketingController),
    SocialAuthController: Object.assign(SocialAuthController, SocialAuthController),
    RegistrationController: Object.assign(RegistrationController, RegistrationController),
    EventOnboardingController: Object.assign(EventOnboardingController, EventOnboardingController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    BusinessController: Object.assign(BusinessController, BusinessController),
    AdminController: Object.assign(AdminController, AdminController),
    EventController: Object.assign(EventController, EventController),
    StripeWebhookController: Object.assign(StripeWebhookController, StripeWebhookController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers