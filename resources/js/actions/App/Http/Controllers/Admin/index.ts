import UserController from './UserController'
import ImpersonationController from './ImpersonationController'
import AuditLogController from './AuditLogController'
import SettingsController from './SettingsController'
import EventModerationController from './EventModerationController'
const Admin = {
    UserController: Object.assign(UserController, UserController),
ImpersonationController: Object.assign(ImpersonationController, ImpersonationController),
AuditLogController: Object.assign(AuditLogController, AuditLogController),
SettingsController: Object.assign(SettingsController, SettingsController),
EventModerationController: Object.assign(EventModerationController, EventModerationController),
}

export default Admin