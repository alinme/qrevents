import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:231
* @route '/events/{event}/tables'
*/
export const store = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/events/{event}/tables',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:231
* @route '/events/{event}/tables'
*/
store.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:231
* @route '/events/{event}/tables'
*/
store.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:231
* @route '/events/{event}/tables'
*/
const storeForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:231
* @route '/events/{event}/tables'
*/
storeForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:243
* @route '/events/{event}/tables/{eventTable}'
*/
export const update = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/events/{event}/tables/{eventTable}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:243
* @route '/events/{event}/tables/{eventTable}'
*/
update.url = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            eventTable: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        eventTable: typeof args.eventTable === 'object'
        ? args.eventTable.id
        : args.eventTable,
    }

    return update.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{eventTable}', parsedArgs.eventTable.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:243
* @route '/events/{event}/tables/{eventTable}'
*/
update.patch = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:243
* @route '/events/{event}/tables/{eventTable}'
*/
const updateForm = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:243
* @route '/events/{event}/tables/{eventTable}'
*/
updateForm.patch = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:267
* @route '/events/{event}/tables/{eventTable}'
*/
export const destroy = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/events/{event}/tables/{eventTable}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:267
* @route '/events/{event}/tables/{eventTable}'
*/
destroy.url = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            eventTable: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        eventTable: typeof args.eventTable === 'object'
        ? args.eventTable.id
        : args.eventTable,
    }

    return destroy.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{eventTable}', parsedArgs.eventTable.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:267
* @route '/events/{event}/tables/{eventTable}'
*/
destroy.delete = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:267
* @route '/events/{event}/tables/{eventTable}'
*/
const destroyForm = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see app/Http/Controllers/EventController.php:267
* @route '/events/{event}/tables/{eventTable}'
*/
destroyForm.delete = (args: { event: number | { id: number }, eventTable: string | number | { id: string | number } } | [event: number | { id: number }, eventTable: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const tables = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default tables