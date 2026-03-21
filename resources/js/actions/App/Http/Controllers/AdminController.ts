import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
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
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
* @route '/admin'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AdminController::index
* @see app/Http/Controllers/AdminController.php:22
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

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:136
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
* @see app/Http/Controllers/AdminController.php:136
* @route '/admin/plans'
*/
storePlan.url = (options?: RouteQueryOptions) => {
    return storePlan.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:136
* @route '/admin/plans'
*/
storePlan.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storePlan.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:136
* @route '/admin/plans'
*/
const storePlanForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storePlan.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::storePlan
* @see app/Http/Controllers/AdminController.php:136
* @route '/admin/plans'
*/
storePlanForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storePlan.url(options),
    method: 'post',
})

storePlan.form = storePlanForm

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:150
* @route '/admin/plans/{plan}'
*/
export const updatePlan = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePlan.url(args, options),
    method: 'patch',
})

updatePlan.definition = {
    methods: ["patch"],
    url: '/admin/plans/{plan}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:150
* @route '/admin/plans/{plan}'
*/
updatePlan.url = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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
* @see app/Http/Controllers/AdminController.php:150
* @route '/admin/plans/{plan}'
*/
updatePlan.patch = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePlan.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\AdminController::updatePlan
* @see app/Http/Controllers/AdminController.php:150
* @route '/admin/plans/{plan}'
*/
const updatePlanForm = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see app/Http/Controllers/AdminController.php:150
* @route '/admin/plans/{plan}'
*/
updatePlanForm.patch = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePlan.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePlan.form = updatePlanForm

/**
* @see \App\Http\Controllers\AdminController::updateCleanupReview
* @see app/Http/Controllers/AdminController.php:120
* @route '/admin/events/{event}/cleanup-review'
*/
export const updateCleanupReview = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateCleanupReview.url(args, options),
    method: 'post',
})

updateCleanupReview.definition = {
    methods: ["post"],
    url: '/admin/events/{event}/cleanup-review',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::updateCleanupReview
* @see app/Http/Controllers/AdminController.php:120
* @route '/admin/events/{event}/cleanup-review'
*/
updateCleanupReview.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return updateCleanupReview.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::updateCleanupReview
* @see app/Http/Controllers/AdminController.php:120
* @route '/admin/events/{event}/cleanup-review'
*/
updateCleanupReview.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateCleanupReview.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::updateCleanupReview
* @see app/Http/Controllers/AdminController.php:120
* @route '/admin/events/{event}/cleanup-review'
*/
const updateCleanupReviewForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCleanupReview.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::updateCleanupReview
* @see app/Http/Controllers/AdminController.php:120
* @route '/admin/events/{event}/cleanup-review'
*/
updateCleanupReviewForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateCleanupReview.url(args, options),
    method: 'post',
})

updateCleanupReview.form = updateCleanupReviewForm

/**
* @see \App\Http\Controllers\AdminController::cleanupEvent
* @see app/Http/Controllers/AdminController.php:109
* @route '/admin/events/{event}/cleanup'
*/
export const cleanupEvent = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanupEvent.url(args, options),
    method: 'post',
})

cleanupEvent.definition = {
    methods: ["post"],
    url: '/admin/events/{event}/cleanup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::cleanupEvent
* @see app/Http/Controllers/AdminController.php:109
* @route '/admin/events/{event}/cleanup'
*/
cleanupEvent.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return cleanupEvent.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::cleanupEvent
* @see app/Http/Controllers/AdminController.php:109
* @route '/admin/events/{event}/cleanup'
*/
cleanupEvent.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanupEvent.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanupEvent
* @see app/Http/Controllers/AdminController.php:109
* @route '/admin/events/{event}/cleanup'
*/
const cleanupEventForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanupEvent.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanupEvent
* @see app/Http/Controllers/AdminController.php:109
* @route '/admin/events/{event}/cleanup'
*/
cleanupEventForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanupEvent.url(args, options),
    method: 'post',
})

cleanupEvent.form = cleanupEventForm

const AdminController = { index, users, events, plans, billing, cleanup, storePlan, updatePlan, updateCleanupReview, cleanupEvent }

export default AdminController