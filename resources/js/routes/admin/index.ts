import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import events735790 from './events'
import plansDaef78 from './plans'
/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
export const overview = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})

overview.definition = {
    methods: ["get","head"],
    url: '/admin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
overview.url = (options?: RouteQueryOptions) => {
    return overview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
overview.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
overview.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overview.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
const overviewForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
overviewForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
overviewForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: overview.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

overview.form = overviewForm

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
export const users = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

users.definition = {
    methods: ["get","head"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
users.url = (options?: RouteQueryOptions) => {
    return users.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
users.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
users.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: users.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
const usersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: users.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
usersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: users.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::users
* @see app/Http/Controllers/AdminController.php:38
* @route '/admin/users'
*/
usersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: users.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

users.form = usersForm

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
export const events = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

events.definition = {
    methods: ["get","head"],
    url: '/admin/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
events.url = (options?: RouteQueryOptions) => {
    return events.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
events.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
events.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: events.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
const eventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
eventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:52
* @route '/admin/events'
*/
eventsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

events.form = eventsForm

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
export const plans = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

plans.definition = {
    methods: ["get","head"],
    url: '/admin/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
plans.url = (options?: RouteQueryOptions) => {
    return plans.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
plans.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
plans.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plans.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
const plansForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
plansForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:81
* @route '/admin/plans'
*/
plansForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: plans.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

plans.form = plansForm

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
export const billing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billing.url(options),
    method: 'get',
})

billing.definition = {
    methods: ["get","head"],
    url: '/admin/billing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
billing.url = (options?: RouteQueryOptions) => {
    return billing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
billing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
billing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: billing.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
const billingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
billingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::billing
* @see app/Http/Controllers/AdminController.php:66
* @route '/admin/billing'
*/
billingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billing.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

billing.form = billingForm

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
export const cleanup = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cleanup.url(options),
    method: 'get',
})

cleanup.definition = {
    methods: ["get","head"],
    url: '/admin/cleanup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
cleanup.url = (options?: RouteQueryOptions) => {
    return cleanup.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
cleanup.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cleanup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
cleanup.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cleanup.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
const cleanupForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cleanup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
cleanupForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cleanup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:95
* @route '/admin/cleanup'
*/
cleanupForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cleanup.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

cleanup.form = cleanupForm

const admin = {
    overview: Object.assign(overview, overview),
    users: Object.assign(users, users),
    events: Object.assign(events, events735790),
    plans: Object.assign(plans, plansDaef78),
    billing: Object.assign(billing, billing),
    cleanup: Object.assign(cleanup, cleanup),
}

export default admin