import DashboardController from './DashboardController'
import EventController from './EventController'
import EventOnboardingController from './EventOnboardingController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    EventOnboardingController: Object.assign(EventOnboardingController, EventOnboardingController),
    EventController: Object.assign(EventController, EventController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers