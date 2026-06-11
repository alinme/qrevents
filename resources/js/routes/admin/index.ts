import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import plansDaef78 from './plans'
/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
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
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
overview.url = (options?: RouteQueryOptions) => {
    return overview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
overview.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
overview.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overview.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
const overviewForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
overviewForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: overview.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::overview
* @see app/Http/Controllers/AdminController.php:19
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
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
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
* @see app/Http/Controllers/AdminController.php:37
* @route '/admin/events'
*/
events.url = (options?: RouteQueryOptions) => {
    return events.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
* @route '/admin/events'
*/
events.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
* @route '/admin/events'
*/
events.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: events.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
* @route '/admin/events'
*/
const eventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
* @route '/admin/events'
*/
eventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: events.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::events
* @see app/Http/Controllers/AdminController.php:37
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
* @see app/Http/Controllers/AdminController.php:77
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
* @see app/Http/Controllers/AdminController.php:77
* @route '/admin/plans'
*/
plans.url = (options?: RouteQueryOptions) => {
    return plans.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:77
* @route '/admin/plans'
*/
plans.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:77
* @route '/admin/plans'
*/
plans.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plans.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:77
* @route '/admin/plans'
*/
const plansForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:77
* @route '/admin/plans'
*/
plansForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: plans.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::plans
* @see app/Http/Controllers/AdminController.php:77
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

const admin = {
    overview: Object.assign(overview, overview),
    events: Object.assign(events, events),
    plans: Object.assign(plans, plansDaef78),
}

export default admin