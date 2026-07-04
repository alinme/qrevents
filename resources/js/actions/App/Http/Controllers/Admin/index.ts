import UserController from './UserController'
import ImpersonationController from './ImpersonationController'
import AuditLogController from './AuditLogController'
import EventModerationController from './EventModerationController'
const Admin = {
    UserController: Object.assign(UserController, UserController),
ImpersonationController: Object.assign(ImpersonationController, ImpersonationController),
AuditLogController: Object.assign(AuditLogController, AuditLogController),
EventModerationController: Object.assign(EventModerationController, EventModerationController),
}

export default Admin