import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::bulkUpdate
* @see app/Http/Controllers/EventController.php:280
* @route '/events/{event}/guests/invitations/bulk-update'
*/
export const bulkUpdate = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdate.url(args, options),
    method: 'post',
})

bulkUpdate.definition = {
    methods: ["post"],
    url: '/events/{event}/guests/invitations/bulk-update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkUpdate
* @see app/Http/Controllers/EventController.php:280
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdate.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkUpdate.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkUpdate
* @see app/Http/Controllers/EventController.php:280
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdate.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdate
* @see app/Http/Controllers/EventController.php:280
* @route '/events/{event}/guests/invitations/bulk-update'
*/
const bulkUpdateForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdate
* @see app/Http/Controllers/EventController.php:280
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdateForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdate.url(args, options),
    method: 'post',
})

bulkUpdate.form = bulkUpdateForm

const invitations = {
    bulkUpdate: Object.assign(bulkUpdate, bulkUpdate),
}

export default invitations