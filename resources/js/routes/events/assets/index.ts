import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import moderation from './moderation'
import wallVisibility from './wall-visibility'
/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:537
* @route '/events/{event}/assets/bulk-delete'
*/
export const bulkDestroy = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroy.url(args, options),
    method: 'post',
})

bulkDestroy.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:537
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroy.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkDestroy.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:537
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroy.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:537
* @route '/events/{event}/assets/bulk-delete'
*/
const bulkDestroyForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:537
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroyForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroy.url(args, options),
    method: 'post',
})

bulkDestroy.form = bulkDestroyForm

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:560
* @route '/events/{event}/assets/bulk-moderation'
*/
export const bulkModeration = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkModeration.url(args, options),
    method: 'post',
})

bulkModeration.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-moderation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:560
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModeration.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkModeration.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:560
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModeration.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:560
* @route '/events/{event}/assets/bulk-moderation'
*/
const bulkModerationForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:560
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModerationForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkModeration.url(args, options),
    method: 'post',
})

bulkModeration.form = bulkModerationForm

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:521
* @route '/events/{event}/assets/{asset}'
*/
export const destroy = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/events/{event}/assets/{asset}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:521
* @route '/events/{event}/assets/{asset}'
*/
destroy.url = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return destroy.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:521
* @route '/events/{event}/assets/{asset}'
*/
destroy.delete = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:521
* @route '/events/{event}/assets/{asset}'
*/
const destroyForm = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:521
* @route '/events/{event}/assets/{asset}'
*/
destroyForm.delete = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const assets = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
    bulkModeration: Object.assign(bulkModeration, bulkModeration),
    destroy: Object.assign(destroy, destroy),
    moderation: Object.assign(moderation, moderation),
    wallVisibility: Object.assign(wallVisibility, wallVisibility),
}

export default assets