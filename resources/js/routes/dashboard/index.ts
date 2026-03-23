import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import businessE58f3e from './business'
/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
export const account = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})

account.definition = {
    methods: ["get","head"],
    url: '/dashboard/account',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
account.url = (options?: RouteQueryOptions) => {
    return account.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
account.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
account.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: account.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
const accountForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
accountForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:62
* @route '/dashboard/account'
*/
accountForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

account.form = accountForm

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
export const business = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

business.definition = {
    methods: ["get","head"],
    url: '/dashboard/business',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
business.url = (options?: RouteQueryOptions) => {
    return business.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
business.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
business.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: business.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
const businessForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
businessForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:85
* @route '/dashboard/business'
*/
businessForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

business.form = businessForm

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
export const events = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

events.definition = {
    methods: ["get","head"],
    url: '/dashboard/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
events.url = (options?: RouteQueryOptions) => {
    return events.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
events.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
events.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: events.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
const eventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
*/
eventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::events
* @see app/Http/Controllers/DashboardController.php:263
* @route '/dashboard/events'
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
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
export const activity = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: activity.url(options),
    method: 'get',
})

activity.definition = {
    methods: ["get","head"],
    url: '/dashboard/activity',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
activity.url = (options?: RouteQueryOptions) => {
    return activity.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
activity.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: activity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
activity.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: activity.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
const activityForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: activity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
activityForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: activity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::activity
* @see app/Http/Controllers/DashboardController.php:268
* @route '/dashboard/activity'
*/
activityForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: activity.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

activity.form = activityForm

const dashboard = {
    account: Object.assign(account, account),
    business: Object.assign(business, businessE58f3e),
    events: Object.assign(events, events),
    activity: Object.assign(activity, activity),
}

export default dashboard