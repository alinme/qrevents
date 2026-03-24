import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:578
* @route '/events/{event}/assets/{asset}/moderation'
*/
export const update = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/events/{event}/assets/{asset}/moderation',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:578
* @route '/events/{event}/assets/{asset}/moderation'
*/
update.url = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:578
* @route '/events/{event}/assets/{asset}/moderation'
*/
update.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:578
* @route '/events/{event}/assets/{asset}/moderation'
*/
const updateForm = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see app/Http/Controllers/EventController.php:578
* @route '/events/{event}/assets/{asset}/moderation'
*/
updateForm.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const moderation = {
    update: Object.assign(update, update),
}

export default moderation