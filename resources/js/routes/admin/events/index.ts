import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::cleanupReview
* @see app/Http/Controllers/AdminController.php:122
* @route '/admin/events/{event}/cleanup-review'
*/
export const cleanupReview = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanupReview.url(args, options),
    method: 'post',
})

cleanupReview.definition = {
    methods: ["post"],
    url: '/admin/events/{event}/cleanup-review',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::cleanupReview
* @see app/Http/Controllers/AdminController.php:122
* @route '/admin/events/{event}/cleanup-review'
*/
cleanupReview.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return cleanupReview.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::cleanupReview
* @see app/Http/Controllers/AdminController.php:122
* @route '/admin/events/{event}/cleanup-review'
*/
cleanupReview.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanupReview.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanupReview
* @see app/Http/Controllers/AdminController.php:122
* @route '/admin/events/{event}/cleanup-review'
*/
const cleanupReviewForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanupReview.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanupReview
* @see app/Http/Controllers/AdminController.php:122
* @route '/admin/events/{event}/cleanup-review'
*/
cleanupReviewForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanupReview.url(args, options),
    method: 'post',
})

cleanupReview.form = cleanupReviewForm

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:111
* @route '/admin/events/{event}/cleanup'
*/
export const cleanup = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanup.url(args, options),
    method: 'post',
})

cleanup.definition = {
    methods: ["post"],
    url: '/admin/events/{event}/cleanup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:111
* @route '/admin/events/{event}/cleanup'
*/
cleanup.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return cleanup.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:111
* @route '/admin/events/{event}/cleanup'
*/
cleanup.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cleanup.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:111
* @route '/admin/events/{event}/cleanup'
*/
const cleanupForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanup.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AdminController::cleanup
* @see app/Http/Controllers/AdminController.php:111
* @route '/admin/events/{event}/cleanup'
*/
cleanupForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cleanup.url(args, options),
    method: 'post',
})

cleanup.form = cleanupForm

const events = {
    cleanupReview: Object.assign(cleanupReview, cleanupReview),
    cleanup: Object.assign(cleanup, cleanup),
}

export default events