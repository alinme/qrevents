import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:43
* @route '/dashboard'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
export const ownedEvents = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ownedEvents.url(options),
    method: 'get',
})

ownedEvents.definition = {
    methods: ["get","head"],
    url: '/dashboard/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
ownedEvents.url = (options?: RouteQueryOptions) => {
    return ownedEvents.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
ownedEvents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
ownedEvents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ownedEvents.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
const ownedEventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
ownedEventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:359
* @route '/dashboard/events'
*/
ownedEventsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ownedEvents.form = ownedEventsForm

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
export const recentActivity = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentActivity.url(options),
    method: 'get',
})

recentActivity.definition = {
    methods: ["get","head"],
    url: '/dashboard/activity',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
recentActivity.url = (options?: RouteQueryOptions) => {
    return recentActivity.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
recentActivity.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
recentActivity.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recentActivity.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
const recentActivityForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
recentActivityForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:364
* @route '/dashboard/activity'
*/
recentActivityForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

recentActivity.form = recentActivityForm

const DashboardController = { index, ownedEvents, recentActivity }

export default DashboardController