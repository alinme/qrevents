import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
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
 * @see app/Http/Controllers/DashboardController.php:65
 * @route '/dashboard/events'
 */
events.url = (options?: RouteQueryOptions) => {
    return events.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
 * @route '/dashboard/events'
 */
events.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
 * @route '/dashboard/events'
 */
events.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: events.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
 * @route '/dashboard/events'
 */
    const eventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: events.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
 * @route '/dashboard/events'
 */
        eventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: events.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::events
 * @see app/Http/Controllers/DashboardController.php:65
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
 * @see app/Http/Controllers/DashboardController.php:70
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
 * @see app/Http/Controllers/DashboardController.php:70
 * @route '/dashboard/activity'
 */
activity.url = (options?: RouteQueryOptions) => {
    return activity.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::activity
 * @see app/Http/Controllers/DashboardController.php:70
 * @route '/dashboard/activity'
 */
activity.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: activity.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::activity
 * @see app/Http/Controllers/DashboardController.php:70
 * @route '/dashboard/activity'
 */
activity.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: activity.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::activity
 * @see app/Http/Controllers/DashboardController.php:70
 * @route '/dashboard/activity'
 */
    const activityForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: activity.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::activity
 * @see app/Http/Controllers/DashboardController.php:70
 * @route '/dashboard/activity'
 */
        activityForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: activity.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::activity
 * @see app/Http/Controllers/DashboardController.php:70
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
    events: Object.assign(events, events),
activity: Object.assign(activity, activity),
}

export default dashboard