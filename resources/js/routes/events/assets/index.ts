import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import moderation from './moderation'
import wallVisibility from './wall-visibility'
/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:791
* @route '/events/{event}/assets/bulk-delete'
*/
export const bulkDestroy = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroy.url(args, options),
    method: 'post',
})

bulkDestroy.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:791
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroy.url = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
* @see app/Http/Controllers/EventController.php:791
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroy.post = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:791
* @route '/events/{event}/assets/bulk-delete'
*/
const bulkDestroyForm = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroy
* @see app/Http/Controllers/EventController.php:791
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroyForm.post = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroy.url(args, options),
    method: 'post',
})

bulkDestroy.form = bulkDestroyForm

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:814
* @route '/events/{event}/assets/bulk-moderation'
*/
export const bulkModeration = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkModeration.url(args, options),
    method: 'post',
})

bulkModeration.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-moderation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:814
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModeration.url = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
* @see app/Http/Controllers/EventController.php:814
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModeration.post = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:814
* @route '/events/{event}/assets/bulk-moderation'
*/
const bulkModerationForm = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkModeration
* @see app/Http/Controllers/EventController.php:814
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkModerationForm.post = (args: { event: string | number | { id: string | number } } | [event: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkModeration.url(args, options),
    method: 'post',
})

bulkModeration.form = bulkModerationForm

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:775
* @route '/events/{event}/assets/{asset}'
*/
export const destroy = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/events/{event}/assets/{asset}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:775
* @route '/events/{event}/assets/{asset}'
*/
destroy.url = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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
* @see app/Http/Controllers/EventController.php:775
* @route '/events/{event}/assets/{asset}'
*/
destroy.delete = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:775
* @route '/events/{event}/assets/{asset}'
*/
const destroyForm = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see app/Http/Controllers/EventController.php:775
* @route '/events/{event}/assets/{asset}'
*/
destroyForm.delete = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
export const thumbnail = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: thumbnail.url(args, options),
    method: 'get',
})

thumbnail.definition = {
    methods: ["get","head"],
    url: '/events/{event}/assets/{asset}/thumbnail',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
thumbnail.url = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return thumbnail.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
thumbnail.get = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: thumbnail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
thumbnail.head = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: thumbnail.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
const thumbnailForm = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: thumbnail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
thumbnailForm.get = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: thumbnail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::thumbnail
* @see app/Http/Controllers/EventController.php:134
* @route '/events/{event}/assets/{asset}/thumbnail'
*/
thumbnailForm.head = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: thumbnail.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

thumbnail.form = thumbnailForm

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
export const preview = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/events/{event}/assets/{asset}/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
preview.url = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return preview.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
preview.get = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
preview.head = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
const previewForm = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
previewForm.get = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::preview
* @see app/Http/Controllers/EventController.php:145
* @route '/events/{event}/assets/{asset}/preview'
*/
previewForm.head = (args: { event: string | number | { id: string | number }, asset: string | number | { id: string | number } } | [event: string | number | { id: string | number }, asset: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

preview.form = previewForm

const assets = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
    bulkModeration: Object.assign(bulkModeration, bulkModeration),
    destroy: Object.assign(destroy, destroy),
    moderation: Object.assign(moderation, moderation),
    wallVisibility: Object.assign(wallVisibility, wallVisibility),
    thumbnail: Object.assign(thumbnail, thumbnail),
    preview: Object.assign(preview, preview),
}

export default assets