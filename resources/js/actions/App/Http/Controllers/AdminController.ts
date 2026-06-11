import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:19
* @route '/admin'
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

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:88
* @route '/admin/plans'
*/
export const storePlan = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storePlan.url(options),
    method: 'post',
})

storePlan.definition = {
    methods: ["post"],
    url: '/admin/plans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:88
* @route '/admin/plans'
*/
storePlan.url = (options?: RouteQueryOptions) => {
    return storePlan.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:88
* @route '/admin/plans'
*/
storePlan.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storePlan.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:88
* @route '/admin/plans'
*/
const storePlanForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storePlan.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:88
* @route '/admin/plans'
*/
storePlanForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storePlan.url(options),
    method: 'post',
})

storePlan.form = storePlanForm

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:102
* @route '/admin/plans/{plan}'
*/
export const updatePlan = (args: { plan: string | number | { id: string | number } } | [plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePlan.url(args, options),
    method: 'patch',
})

updatePlan.definition = {
    methods: ["patch"],
    url: '/admin/plans/{plan}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:102
* @route '/admin/plans/{plan}'
*/
updatePlan.url = (args: { plan: string | number | { id: string | number } } | [plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { plan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            plan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        plan: typeof args.plan === 'object'
        ? args.plan.id
        : args.plan,
    }

    return updatePlan.definition.url
            .replace('{plan}', parsedArgs.plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:102
* @route '/admin/plans/{plan}'
*/
updatePlan.patch = (args: { plan: string | number | { id: string | number } } | [plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePlan.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:102
* @route '/admin/plans/{plan}'
*/
const updatePlanForm = (args: { plan: string | number | { id: string | number } } | [plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePlan.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:102
* @route '/admin/plans/{plan}'
*/
updatePlanForm.patch = (args: { plan: string | number | { id: string | number } } | [plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePlan.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePlan.form = updatePlanForm

const AdminController = { index, events, plans, storePlan, updatePlan }

export default AdminController