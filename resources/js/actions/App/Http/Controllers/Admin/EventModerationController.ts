import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\EventModerationController::suspend
 * @see app/Http/Controllers/Admin/EventModerationController.php:15
 * @route '/admin/events/{event}/suspend'
 */
export const suspend = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suspend.url(args, options),
    method: 'post',
})

suspend.definition = {
    methods: ["post"],
    url: '/admin/events/{event}/suspend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\EventModerationController::suspend
 * @see app/Http/Controllers/Admin/EventModerationController.php:15
 * @route '/admin/events/{event}/suspend'
 */
suspend.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return suspend.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\EventModerationController::suspend
 * @see app/Http/Controllers/Admin/EventModerationController.php:15
 * @route '/admin/events/{event}/suspend'
 */
suspend.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suspend.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\EventModerationController::suspend
 * @see app/Http/Controllers/Admin/EventModerationController.php:15
 * @route '/admin/events/{event}/suspend'
 */
    const suspendForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: suspend.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\EventModerationController::suspend
 * @see app/Http/Controllers/Admin/EventModerationController.php:15
 * @route '/admin/events/{event}/suspend'
 */
        suspendForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: suspend.url(args, options),
            method: 'post',
        })
    
    suspend.form = suspendForm
/**
* @see \App\Http\Controllers\Admin\EventModerationController::extend
 * @see app/Http/Controllers/Admin/EventModerationController.php:34
 * @route '/admin/events/{event}/extend'
 */
export const extend = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: extend.url(args, options),
    method: 'patch',
})

extend.definition = {
    methods: ["patch"],
    url: '/admin/events/{event}/extend',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\EventModerationController::extend
 * @see app/Http/Controllers/Admin/EventModerationController.php:34
 * @route '/admin/events/{event}/extend'
 */
extend.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return extend.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\EventModerationController::extend
 * @see app/Http/Controllers/Admin/EventModerationController.php:34
 * @route '/admin/events/{event}/extend'
 */
extend.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: extend.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\EventModerationController::extend
 * @see app/Http/Controllers/Admin/EventModerationController.php:34
 * @route '/admin/events/{event}/extend'
 */
    const extendForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: extend.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\EventModerationController::extend
 * @see app/Http/Controllers/Admin/EventModerationController.php:34
 * @route '/admin/events/{event}/extend'
 */
        extendForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: extend.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    extend.form = extendForm
/**
* @see \App\Http\Controllers\Admin\EventModerationController::destroy
 * @see app/Http/Controllers/Admin/EventModerationController.php:70
 * @route '/admin/events/{event}'
 */
export const destroy = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/events/{event}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\EventModerationController::destroy
 * @see app/Http/Controllers/Admin/EventModerationController.php:70
 * @route '/admin/events/{event}'
 */
destroy.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\EventModerationController::destroy
 * @see app/Http/Controllers/Admin/EventModerationController.php:70
 * @route '/admin/events/{event}'
 */
destroy.delete = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\EventModerationController::destroy
 * @see app/Http/Controllers/Admin/EventModerationController.php:70
 * @route '/admin/events/{event}'
 */
    const destroyForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\EventModerationController::destroy
 * @see app/Http/Controllers/Admin/EventModerationController.php:70
 * @route '/admin/events/{event}'
 */
        destroyForm.delete = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const EventModerationController = { suspend, extend, destroy }

export default EventModerationController