import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
export const show = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/events/{event}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
show.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
show.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
show.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
const showForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
showForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}'
*/
showForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
export const guests = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guests.url(args, options),
    method: 'get',
})

guests.definition = {
    methods: ["get","head"],
    url: '/events/{event}/guests',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
guests.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return guests.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
guests.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
guests.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: guests.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
const guestsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
guestsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:104
* @route '/events/{event}/guests'
*/
guestsForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

guests.form = guestsForm

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
export const guestReport = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestReport.url(args, options),
    method: 'get',
})

guestReport.definition = {
    methods: ["get","head"],
    url: '/events/{event}/guests/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
guestReport.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return guestReport.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
guestReport.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestReport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
guestReport.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: guestReport.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
const guestReportForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestReport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
guestReportForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestReport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestReport
* @see app/Http/Controllers/EventController.php:114
* @route '/events/{event}/guests/report'
*/
guestReportForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestReport.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

guestReport.form = guestReportForm

/**
* @see \App\Http\Controllers\EventController::storeGuestParty
* @see app/Http/Controllers/EventController.php:124
* @route '/events/{event}/guests'
*/
export const storeGuestParty = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeGuestParty.url(args, options),
    method: 'post',
})

storeGuestParty.definition = {
    methods: ["post"],
    url: '/events/{event}/guests',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::storeGuestParty
* @see app/Http/Controllers/EventController.php:124
* @route '/events/{event}/guests'
*/
storeGuestParty.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return storeGuestParty.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::storeGuestParty
* @see app/Http/Controllers/EventController.php:124
* @route '/events/{event}/guests'
*/
storeGuestParty.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeGuestParty.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeGuestParty
* @see app/Http/Controllers/EventController.php:124
* @route '/events/{event}/guests'
*/
const storeGuestPartyForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeGuestParty.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeGuestParty
* @see app/Http/Controllers/EventController.php:124
* @route '/events/{event}/guests'
*/
storeGuestPartyForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeGuestParty.url(args, options),
    method: 'post',
})

storeGuestParty.form = storeGuestPartyForm

/**
* @see \App\Http\Controllers\EventController::importGuestParties
* @see app/Http/Controllers/EventController.php:159
* @route '/events/{event}/guests/import'
*/
export const importGuestParties = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importGuestParties.url(args, options),
    method: 'post',
})

importGuestParties.definition = {
    methods: ["post"],
    url: '/events/{event}/guests/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::importGuestParties
* @see app/Http/Controllers/EventController.php:159
* @route '/events/{event}/guests/import'
*/
importGuestParties.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return importGuestParties.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::importGuestParties
* @see app/Http/Controllers/EventController.php:159
* @route '/events/{event}/guests/import'
*/
importGuestParties.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importGuestParties.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::importGuestParties
* @see app/Http/Controllers/EventController.php:159
* @route '/events/{event}/guests/import'
*/
const importGuestPartiesForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importGuestParties.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::importGuestParties
* @see app/Http/Controllers/EventController.php:159
* @route '/events/{event}/guests/import'
*/
importGuestPartiesForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importGuestParties.url(args, options),
    method: 'post',
})

importGuestParties.form = importGuestPartiesForm

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
export const exportGuestLedger = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportGuestLedger.url(args, options),
    method: 'get',
})

exportGuestLedger.definition = {
    methods: ["get","head"],
    url: '/events/{event}/guests/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
exportGuestLedger.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return exportGuestLedger.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
exportGuestLedger.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportGuestLedger.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
exportGuestLedger.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportGuestLedger.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
const exportGuestLedgerForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportGuestLedger.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
exportGuestLedgerForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportGuestLedger.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportGuestLedger
* @see app/Http/Controllers/EventController.php:206
* @route '/events/{event}/guests/export'
*/
exportGuestLedgerForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportGuestLedger.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportGuestLedger.form = exportGuestLedgerForm

/**
* @see \App\Http\Controllers\EventController::bulkUpdateGuestInvitations
* @see app/Http/Controllers/EventController.php:288
* @route '/events/{event}/guests/invitations/bulk-update'
*/
export const bulkUpdateGuestInvitations = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdateGuestInvitations.url(args, options),
    method: 'post',
})

bulkUpdateGuestInvitations.definition = {
    methods: ["post"],
    url: '/events/{event}/guests/invitations/bulk-update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkUpdateGuestInvitations
* @see app/Http/Controllers/EventController.php:288
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdateGuestInvitations.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkUpdateGuestInvitations.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkUpdateGuestInvitations
* @see app/Http/Controllers/EventController.php:288
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdateGuestInvitations.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdateGuestInvitations.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdateGuestInvitations
* @see app/Http/Controllers/EventController.php:288
* @route '/events/{event}/guests/invitations/bulk-update'
*/
const bulkUpdateGuestInvitationsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdateGuestInvitations.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdateGuestInvitations
* @see app/Http/Controllers/EventController.php:288
* @route '/events/{event}/guests/invitations/bulk-update'
*/
bulkUpdateGuestInvitationsForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdateGuestInvitations.url(args, options),
    method: 'post',
})

bulkUpdateGuestInvitations.form = bulkUpdateGuestInvitationsForm

/**
* @see \App\Http\Controllers\EventController::updateInvitationSettings
* @see app/Http/Controllers/EventController.php:340
* @route '/events/{event}/guests/invitation-settings'
*/
export const updateInvitationSettings = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateInvitationSettings.url(args, options),
    method: 'patch',
})

updateInvitationSettings.definition = {
    methods: ["patch"],
    url: '/events/{event}/guests/invitation-settings',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateInvitationSettings
* @see app/Http/Controllers/EventController.php:340
* @route '/events/{event}/guests/invitation-settings'
*/
updateInvitationSettings.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateInvitationSettings.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateInvitationSettings
* @see app/Http/Controllers/EventController.php:340
* @route '/events/{event}/guests/invitation-settings'
*/
updateInvitationSettings.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateInvitationSettings.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateInvitationSettings
* @see app/Http/Controllers/EventController.php:340
* @route '/events/{event}/guests/invitation-settings'
*/
const updateInvitationSettingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateInvitationSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateInvitationSettings
* @see app/Http/Controllers/EventController.php:340
* @route '/events/{event}/guests/invitation-settings'
*/
updateInvitationSettingsForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateInvitationSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateInvitationSettings.form = updateInvitationSettingsForm

/**
* @see \App\Http\Controllers\EventController::updateGuestParty
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
export const updateGuestParty = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateGuestParty.url(args, options),
    method: 'patch',
})

updateGuestParty.definition = {
    methods: ["patch"],
    url: '/events/{event}/guests/{guestParty}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateGuestParty
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
updateGuestParty.url = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            guestParty: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        guestParty: typeof args.guestParty === 'object'
        ? args.guestParty.id
        : args.guestParty,
    }

    return updateGuestParty.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{guestParty}', parsedArgs.guestParty.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateGuestParty
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
updateGuestParty.patch = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateGuestParty.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateGuestParty
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
const updateGuestPartyForm = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateGuestParty.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateGuestParty
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
updateGuestPartyForm.patch = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateGuestParty.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateGuestParty.form = updateGuestPartyForm

/**
* @see \App\Http\Controllers\EventController::destroyGuestParty
* @see app/Http/Controllers/EventController.php:149
* @route '/events/{event}/guests/{guestParty}'
*/
export const destroyGuestParty = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyGuestParty.url(args, options),
    method: 'delete',
})

destroyGuestParty.definition = {
    methods: ["delete"],
    url: '/events/{event}/guests/{guestParty}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroyGuestParty
* @see app/Http/Controllers/EventController.php:149
* @route '/events/{event}/guests/{guestParty}'
*/
destroyGuestParty.url = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            guestParty: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        guestParty: typeof args.guestParty === 'object'
        ? args.guestParty.id
        : args.guestParty,
    }

    return destroyGuestParty.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{guestParty}', parsedArgs.guestParty.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::destroyGuestParty
* @see app/Http/Controllers/EventController.php:149
* @route '/events/{event}/guests/{guestParty}'
*/
destroyGuestParty.delete = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyGuestParty.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroyGuestParty
* @see app/Http/Controllers/EventController.php:149
* @route '/events/{event}/guests/{guestParty}'
*/
const destroyGuestPartyForm = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyGuestParty.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::destroyGuestParty
* @see app/Http/Controllers/EventController.php:149
* @route '/events/{event}/guests/{guestParty}'
*/
destroyGuestPartyForm.delete = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyGuestParty.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyGuestParty.form = destroyGuestPartyForm

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
export const media = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

media.definition = {
    methods: ["get","head"],
    url: '/events/{event}/media',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
media.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return media.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
media.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
media.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: media.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
const mediaForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
mediaForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:82
* @route '/events/{event}/media'
*/
mediaForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

media.form = mediaForm

/**
* @see \App\Http\Controllers\EventController::startMediaExport
* @see app/Http/Controllers/EventController.php:496
* @route '/events/{event}/exports/media'
*/
export const startMediaExport = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: startMediaExport.url(args, options),
    method: 'post',
})

startMediaExport.definition = {
    methods: ["post"],
    url: '/events/{event}/exports/media',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::startMediaExport
* @see app/Http/Controllers/EventController.php:496
* @route '/events/{event}/exports/media'
*/
startMediaExport.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return startMediaExport.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::startMediaExport
* @see app/Http/Controllers/EventController.php:496
* @route '/events/{event}/exports/media'
*/
startMediaExport.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: startMediaExport.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::startMediaExport
* @see app/Http/Controllers/EventController.php:496
* @route '/events/{event}/exports/media'
*/
const startMediaExportForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: startMediaExport.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::startMediaExport
* @see app/Http/Controllers/EventController.php:496
* @route '/events/{event}/exports/media'
*/
startMediaExportForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: startMediaExport.url(args, options),
    method: 'post',
})

startMediaExport.form = startMediaExportForm

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
export const downloadMediaExport = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadMediaExport.url(args, options),
    method: 'get',
})

downloadMediaExport.definition = {
    methods: ["get","head"],
    url: '/events/{event}/exports/media/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
downloadMediaExport.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return downloadMediaExport.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
downloadMediaExport.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadMediaExport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
downloadMediaExport.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadMediaExport.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
const downloadMediaExportForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadMediaExport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
downloadMediaExportForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadMediaExport.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadMediaExport
* @see app/Http/Controllers/EventController.php:536
* @route '/events/{event}/exports/media/download'
*/
downloadMediaExportForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadMediaExport.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

downloadMediaExport.form = downloadMediaExportForm

/**
* @see \App\Http\Controllers\EventController::bulkDestroyAssets
* @see app/Http/Controllers/EventController.php:582
* @route '/events/{event}/assets/bulk-delete'
*/
export const bulkDestroyAssets = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroyAssets.url(args, options),
    method: 'post',
})

bulkDestroyAssets.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkDestroyAssets
* @see app/Http/Controllers/EventController.php:582
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroyAssets.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkDestroyAssets.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkDestroyAssets
* @see app/Http/Controllers/EventController.php:582
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroyAssets.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDestroyAssets.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroyAssets
* @see app/Http/Controllers/EventController.php:582
* @route '/events/{event}/assets/bulk-delete'
*/
const bulkDestroyAssetsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroyAssets.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkDestroyAssets
* @see app/Http/Controllers/EventController.php:582
* @route '/events/{event}/assets/bulk-delete'
*/
bulkDestroyAssetsForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkDestroyAssets.url(args, options),
    method: 'post',
})

bulkDestroyAssets.form = bulkDestroyAssetsForm

/**
* @see \App\Http\Controllers\EventController::bulkUpdateAssetModeration
* @see app/Http/Controllers/EventController.php:605
* @route '/events/{event}/assets/bulk-moderation'
*/
export const bulkUpdateAssetModeration = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdateAssetModeration.url(args, options),
    method: 'post',
})

bulkUpdateAssetModeration.definition = {
    methods: ["post"],
    url: '/events/{event}/assets/bulk-moderation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::bulkUpdateAssetModeration
* @see app/Http/Controllers/EventController.php:605
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkUpdateAssetModeration.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return bulkUpdateAssetModeration.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::bulkUpdateAssetModeration
* @see app/Http/Controllers/EventController.php:605
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkUpdateAssetModeration.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdateAssetModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdateAssetModeration
* @see app/Http/Controllers/EventController.php:605
* @route '/events/{event}/assets/bulk-moderation'
*/
const bulkUpdateAssetModerationForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdateAssetModeration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::bulkUpdateAssetModeration
* @see app/Http/Controllers/EventController.php:605
* @route '/events/{event}/assets/bulk-moderation'
*/
bulkUpdateAssetModerationForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkUpdateAssetModeration.url(args, options),
    method: 'post',
})

bulkUpdateAssetModeration.form = bulkUpdateAssetModerationForm

/**
* @see \App\Http\Controllers\EventController::destroyAsset
* @see app/Http/Controllers/EventController.php:566
* @route '/events/{event}/assets/{asset}'
*/
export const destroyAsset = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyAsset.url(args, options),
    method: 'delete',
})

destroyAsset.definition = {
    methods: ["delete"],
    url: '/events/{event}/assets/{asset}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroyAsset
* @see app/Http/Controllers/EventController.php:566
* @route '/events/{event}/assets/{asset}'
*/
destroyAsset.url = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return destroyAsset.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::destroyAsset
* @see app/Http/Controllers/EventController.php:566
* @route '/events/{event}/assets/{asset}'
*/
destroyAsset.delete = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyAsset.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroyAsset
* @see app/Http/Controllers/EventController.php:566
* @route '/events/{event}/assets/{asset}'
*/
const destroyAssetForm = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyAsset.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::destroyAsset
* @see app/Http/Controllers/EventController.php:566
* @route '/events/{event}/assets/{asset}'
*/
destroyAssetForm.delete = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyAsset.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyAsset.form = destroyAssetForm

/**
* @see \App\Http\Controllers\EventController::updateAssetModeration
* @see app/Http/Controllers/EventController.php:637
* @route '/events/{event}/assets/{asset}/moderation'
*/
export const updateAssetModeration = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateAssetModeration.url(args, options),
    method: 'patch',
})

updateAssetModeration.definition = {
    methods: ["patch"],
    url: '/events/{event}/assets/{asset}/moderation',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateAssetModeration
* @see app/Http/Controllers/EventController.php:637
* @route '/events/{event}/assets/{asset}/moderation'
*/
updateAssetModeration.url = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return updateAssetModeration.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateAssetModeration
* @see app/Http/Controllers/EventController.php:637
* @route '/events/{event}/assets/{asset}/moderation'
*/
updateAssetModeration.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateAssetModeration.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateAssetModeration
* @see app/Http/Controllers/EventController.php:637
* @route '/events/{event}/assets/{asset}/moderation'
*/
const updateAssetModerationForm = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAssetModeration.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateAssetModeration
* @see app/Http/Controllers/EventController.php:637
* @route '/events/{event}/assets/{asset}/moderation'
*/
updateAssetModerationForm.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAssetModeration.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateAssetModeration.form = updateAssetModerationForm

/**
* @see \App\Http\Controllers\EventController::updateAssetWallVisibility
* @see app/Http/Controllers/EventController.php:660
* @route '/events/{event}/assets/{asset}/wall-visibility'
*/
export const updateAssetWallVisibility = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateAssetWallVisibility.url(args, options),
    method: 'patch',
})

updateAssetWallVisibility.definition = {
    methods: ["patch"],
    url: '/events/{event}/assets/{asset}/wall-visibility',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateAssetWallVisibility
* @see app/Http/Controllers/EventController.php:660
* @route '/events/{event}/assets/{asset}/wall-visibility'
*/
updateAssetWallVisibility.url = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return updateAssetWallVisibility.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateAssetWallVisibility
* @see app/Http/Controllers/EventController.php:660
* @route '/events/{event}/assets/{asset}/wall-visibility'
*/
updateAssetWallVisibility.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateAssetWallVisibility.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateAssetWallVisibility
* @see app/Http/Controllers/EventController.php:660
* @route '/events/{event}/assets/{asset}/wall-visibility'
*/
const updateAssetWallVisibilityForm = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAssetWallVisibility.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateAssetWallVisibility
* @see app/Http/Controllers/EventController.php:660
* @route '/events/{event}/assets/{asset}/wall-visibility'
*/
updateAssetWallVisibilityForm.patch = (args: { event: number | { id: number }, asset: number | { id: number } } | [event: number | { id: number }, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAssetWallVisibility.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateAssetWallVisibility.form = updateAssetWallVisibilityForm

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
export const settings = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/events/{event}/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
settings.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return settings.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
settings.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
settings.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
const settingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
settingsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:674
* @route '/events/{event}/settings'
*/
settingsForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

settings.form = settingsForm

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:686
* @route '/events/{event}/settings'
*/
export const updateSettings = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSettings.url(args, options),
    method: 'patch',
})

updateSettings.definition = {
    methods: ["patch"],
    url: '/events/{event}/settings',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:686
* @route '/events/{event}/settings'
*/
updateSettings.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateSettings.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:686
* @route '/events/{event}/settings'
*/
updateSettings.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSettings.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:686
* @route '/events/{event}/settings'
*/
const updateSettingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:686
* @route '/events/{event}/settings'
*/
updateSettingsForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateSettings.form = updateSettingsForm

/**
* @see \App\Http\Controllers\EventController::updateBilling
* @see app/Http/Controllers/EventController.php:1030
* @route '/events/{event}/billing'
*/
export const updateBilling = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateBilling.url(args, options),
    method: 'patch',
})

updateBilling.definition = {
    methods: ["patch"],
    url: '/events/{event}/billing',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateBilling
* @see app/Http/Controllers/EventController.php:1030
* @route '/events/{event}/billing'
*/
updateBilling.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateBilling.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateBilling
* @see app/Http/Controllers/EventController.php:1030
* @route '/events/{event}/billing'
*/
updateBilling.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateBilling.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateBilling
* @see app/Http/Controllers/EventController.php:1030
* @route '/events/{event}/billing'
*/
const updateBillingForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateBilling.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateBilling
* @see app/Http/Controllers/EventController.php:1030
* @route '/events/{event}/billing'
*/
updateBillingForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateBilling.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateBilling.form = updateBillingForm

/**
* @see \App\Http\Controllers\EventController::createBillingCheckout
* @see app/Http/Controllers/EventController.php:1063
* @route '/events/{event}/billing/checkout'
*/
export const createBillingCheckout = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createBillingCheckout.url(args, options),
    method: 'post',
})

createBillingCheckout.definition = {
    methods: ["post"],
    url: '/events/{event}/billing/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::createBillingCheckout
* @see app/Http/Controllers/EventController.php:1063
* @route '/events/{event}/billing/checkout'
*/
createBillingCheckout.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return createBillingCheckout.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::createBillingCheckout
* @see app/Http/Controllers/EventController.php:1063
* @route '/events/{event}/billing/checkout'
*/
createBillingCheckout.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createBillingCheckout.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::createBillingCheckout
* @see app/Http/Controllers/EventController.php:1063
* @route '/events/{event}/billing/checkout'
*/
const createBillingCheckoutForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: createBillingCheckout.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::createBillingCheckout
* @see app/Http/Controllers/EventController.php:1063
* @route '/events/{event}/billing/checkout'
*/
createBillingCheckoutForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: createBillingCheckout.url(args, options),
    method: 'post',
})

createBillingCheckout.form = createBillingCheckoutForm

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:1132
* @route '/events/{event}/collaborators'
*/
export const storeCollaborator = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCollaborator.url(args, options),
    method: 'post',
})

storeCollaborator.definition = {
    methods: ["post"],
    url: '/events/{event}/collaborators',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:1132
* @route '/events/{event}/collaborators'
*/
storeCollaborator.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return storeCollaborator.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:1132
* @route '/events/{event}/collaborators'
*/
storeCollaborator.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCollaborator.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:1132
* @route '/events/{event}/collaborators'
*/
const storeCollaboratorForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCollaborator.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:1132
* @route '/events/{event}/collaborators'
*/
storeCollaboratorForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCollaborator.url(args, options),
    method: 'post',
})

storeCollaborator.form = storeCollaboratorForm

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
export const acceptCollaboratorInvite = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

acceptCollaboratorInvite.definition = {
    methods: ["get","head"],
    url: '/collaborator-invites/{collaborator}/accept',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return acceptCollaboratorInvite.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
const acceptCollaboratorInviteForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInviteForm.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:1204
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInviteForm.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

acceptCollaboratorInvite.form = acceptCollaboratorInviteForm

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:1236
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
export const completeCollaboratorInviteRegistration = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

completeCollaboratorInviteRegistration.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:1236
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistration.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return completeCollaboratorInviteRegistration.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:1236
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistration.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:1236
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
const completeCollaboratorInviteRegistrationForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:1236
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistrationForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

completeCollaboratorInviteRegistration.form = completeCollaboratorInviteRegistrationForm

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:1274
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
export const completeCollaboratorInviteLogin = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

completeCollaboratorInviteLogin.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:1274
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLogin.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return completeCollaboratorInviteLogin.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:1274
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLogin.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:1274
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
const completeCollaboratorInviteLoginForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:1274
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLoginForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

completeCollaboratorInviteLogin.form = completeCollaboratorInviteLoginForm

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
export const guestInvitation = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestInvitation.url(args, options),
    method: 'get',
})

guestInvitation.definition = {
    methods: ["get","head"],
    url: '/invite/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
guestInvitation.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return guestInvitation.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
guestInvitation.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
guestInvitation.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: guestInvitation.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
const guestInvitationForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
guestInvitationForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestInvitation
* @see app/Http/Controllers/EventController.php:354
* @route '/invite/{token}'
*/
guestInvitationForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestInvitation.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

guestInvitation.form = guestInvitationForm

/**
* @see \App\Http\Controllers\EventController::respondToGuestInvitation
* @see app/Http/Controllers/EventController.php:372
* @route '/invite/{token}'
*/
export const respondToGuestInvitation = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: respondToGuestInvitation.url(args, options),
    method: 'post',
})

respondToGuestInvitation.definition = {
    methods: ["post"],
    url: '/invite/{token}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::respondToGuestInvitation
* @see app/Http/Controllers/EventController.php:372
* @route '/invite/{token}'
*/
respondToGuestInvitation.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return respondToGuestInvitation.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::respondToGuestInvitation
* @see app/Http/Controllers/EventController.php:372
* @route '/invite/{token}'
*/
respondToGuestInvitation.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: respondToGuestInvitation.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::respondToGuestInvitation
* @see app/Http/Controllers/EventController.php:372
* @route '/invite/{token}'
*/
const respondToGuestInvitationForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: respondToGuestInvitation.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::respondToGuestInvitation
* @see app/Http/Controllers/EventController.php:372
* @route '/invite/{token}'
*/
respondToGuestInvitationForm.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: respondToGuestInvitation.url(args, options),
    method: 'post',
})

respondToGuestInvitation.form = respondToGuestInvitationForm

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
export const publicInvitation = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: publicInvitation.url(args, options),
    method: 'get',
})

publicInvitation.definition = {
    methods: ["get","head"],
    url: '/invite/public/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
publicInvitation.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return publicInvitation.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
publicInvitation.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: publicInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
publicInvitation.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: publicInvitation.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
const publicInvitationForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: publicInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
publicInvitationForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: publicInvitation.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::publicInvitation
* @see app/Http/Controllers/EventController.php:411
* @route '/invite/public/{token}'
*/
publicInvitationForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: publicInvitation.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

publicInvitation.form = publicInvitationForm

/**
* @see \App\Http\Controllers\EventController::respondToPublicInvitation
* @see app/Http/Controllers/EventController.php:436
* @route '/invite/public/{token}'
*/
export const respondToPublicInvitation = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: respondToPublicInvitation.url(args, options),
    method: 'post',
})

respondToPublicInvitation.definition = {
    methods: ["post"],
    url: '/invite/public/{token}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::respondToPublicInvitation
* @see app/Http/Controllers/EventController.php:436
* @route '/invite/public/{token}'
*/
respondToPublicInvitation.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return respondToPublicInvitation.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::respondToPublicInvitation
* @see app/Http/Controllers/EventController.php:436
* @route '/invite/public/{token}'
*/
respondToPublicInvitation.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: respondToPublicInvitation.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::respondToPublicInvitation
* @see app/Http/Controllers/EventController.php:436
* @route '/invite/public/{token}'
*/
const respondToPublicInvitationForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: respondToPublicInvitation.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::respondToPublicInvitation
* @see app/Http/Controllers/EventController.php:436
* @route '/invite/public/{token}'
*/
respondToPublicInvitationForm.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: respondToPublicInvitation.url(args, options),
    method: 'post',
})

respondToPublicInvitation.form = respondToPublicInvitationForm

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
export const album = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

album.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
album.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return album.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
album.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
album.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: album.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
const albumForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
albumForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1306
* @route '/a/{shareToken}'
*/
albumForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

album.form = albumForm

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
export const albumAssets = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: albumAssets.url(args, options),
    method: 'get',
})

albumAssets.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
albumAssets.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return albumAssets.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
albumAssets.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: albumAssets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
albumAssets.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: albumAssets.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
const albumAssetsForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: albumAssets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
albumAssetsForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: albumAssets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::albumAssets
* @see app/Http/Controllers/EventController.php:1416
* @route '/a/{shareToken}/assets'
*/
albumAssetsForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: albumAssets.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

albumAssets.form = albumAssetsForm

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
export const guestProfile = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestProfile.url(args, options),
    method: 'get',
})

guestProfile.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/guest-profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
guestProfile.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return guestProfile.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
guestProfile.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guestProfile.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
guestProfile.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: guestProfile.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
const guestProfileForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestProfile.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
guestProfileForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestProfile.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guestProfile
* @see app/Http/Controllers/EventController.php:1445
* @route '/a/{shareToken}/guest-profile'
*/
guestProfileForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guestProfile.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

guestProfile.form = guestProfileForm

/**
* @see \App\Http\Controllers\EventController::upsertGuestProfile
* @see app/Http/Controllers/EventController.php:1477
* @route '/a/{shareToken}/guest-profile'
*/
export const upsertGuestProfile = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upsertGuestProfile.url(args, options),
    method: 'post',
})

upsertGuestProfile.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/guest-profile',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::upsertGuestProfile
* @see app/Http/Controllers/EventController.php:1477
* @route '/a/{shareToken}/guest-profile'
*/
upsertGuestProfile.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return upsertGuestProfile.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::upsertGuestProfile
* @see app/Http/Controllers/EventController.php:1477
* @route '/a/{shareToken}/guest-profile'
*/
upsertGuestProfile.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upsertGuestProfile.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upsertGuestProfile
* @see app/Http/Controllers/EventController.php:1477
* @route '/a/{shareToken}/guest-profile'
*/
const upsertGuestProfileForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upsertGuestProfile.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upsertGuestProfile
* @see app/Http/Controllers/EventController.php:1477
* @route '/a/{shareToken}/guest-profile'
*/
upsertGuestProfileForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upsertGuestProfile.url(args, options),
    method: 'post',
})

upsertGuestProfile.form = upsertGuestProfileForm

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1517
* @route '/a/{shareToken}/uploads'
*/
export const upload = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/uploads',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1517
* @route '/a/{shareToken}/uploads'
*/
upload.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return upload.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1517
* @route '/a/{shareToken}/uploads'
*/
upload.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1517
* @route '/a/{shareToken}/uploads'
*/
const uploadForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1517
* @route '/a/{shareToken}/uploads'
*/
uploadForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

upload.form = uploadForm

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:1711
* @route '/a/{shareToken}/text-posts'
*/
export const postText = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: postText.url(args, options),
    method: 'post',
})

postText.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/text-posts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:1711
* @route '/a/{shareToken}/text-posts'
*/
postText.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return postText.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:1711
* @route '/a/{shareToken}/text-posts'
*/
postText.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: postText.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:1711
* @route '/a/{shareToken}/text-posts'
*/
const postTextForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: postText.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:1711
* @route '/a/{shareToken}/text-posts'
*/
postTextForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: postText.url(args, options),
    method: 'post',
})

postText.form = postTextForm

/**
* @see \App\Http\Controllers\EventController::toggleAssetLike
* @see app/Http/Controllers/EventController.php:1863
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
export const toggleAssetLike = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleAssetLike.url(args, options),
    method: 'post',
})

toggleAssetLike.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/likes/toggle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::toggleAssetLike
* @see app/Http/Controllers/EventController.php:1863
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggleAssetLike.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return toggleAssetLike.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::toggleAssetLike
* @see app/Http/Controllers/EventController.php:1863
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggleAssetLike.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleAssetLike.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggleAssetLike
* @see app/Http/Controllers/EventController.php:1863
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
const toggleAssetLikeForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleAssetLike.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggleAssetLike
* @see app/Http/Controllers/EventController.php:1863
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggleAssetLikeForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleAssetLike.url(args, options),
    method: 'post',
})

toggleAssetLike.form = toggleAssetLikeForm

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
export const assetComments = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assetComments.url(args, options),
    method: 'get',
})

assetComments.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets/{asset}/comments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
assetComments.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return assetComments.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
assetComments.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assetComments.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
assetComments.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assetComments.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
const assetCommentsForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetComments.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
assetCommentsForm.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetComments.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetComments
* @see app/Http/Controllers/EventController.php:1920
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
assetCommentsForm.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetComments.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

assetComments.form = assetCommentsForm

/**
* @see \App\Http\Controllers\EventController::storeAssetComment
* @see app/Http/Controllers/EventController.php:1954
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
export const storeAssetComment = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeAssetComment.url(args, options),
    method: 'post',
})

storeAssetComment.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/comments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::storeAssetComment
* @see app/Http/Controllers/EventController.php:1954
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
storeAssetComment.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return storeAssetComment.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::storeAssetComment
* @see app/Http/Controllers/EventController.php:1954
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
storeAssetComment.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeAssetComment.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeAssetComment
* @see app/Http/Controllers/EventController.php:1954
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
const storeAssetCommentForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeAssetComment.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeAssetComment
* @see app/Http/Controllers/EventController.php:1954
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
storeAssetCommentForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeAssetComment.url(args, options),
    method: 'post',
})

storeAssetComment.form = storeAssetCommentForm

/**
* @see \App\Http\Controllers\EventController::toggleAssetCommentLike
* @see app/Http/Controllers/EventController.php:2005
* @route '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle'
*/
export const toggleAssetCommentLike = (args: { shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } } | [shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleAssetCommentLike.url(args, options),
    method: 'post',
})

toggleAssetCommentLike.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::toggleAssetCommentLike
* @see app/Http/Controllers/EventController.php:2005
* @route '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle'
*/
toggleAssetCommentLike.url = (args: { shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } } | [shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
            comment: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
        comment: typeof args.comment === 'object'
        ? args.comment.id
        : args.comment,
    }

    return toggleAssetCommentLike.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace('{comment}', parsedArgs.comment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::toggleAssetCommentLike
* @see app/Http/Controllers/EventController.php:2005
* @route '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle'
*/
toggleAssetCommentLike.post = (args: { shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } } | [shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleAssetCommentLike.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggleAssetCommentLike
* @see app/Http/Controllers/EventController.php:2005
* @route '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle'
*/
const toggleAssetCommentLikeForm = (args: { shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } } | [shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleAssetCommentLike.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggleAssetCommentLike
* @see app/Http/Controllers/EventController.php:2005
* @route '/a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle'
*/
toggleAssetCommentLikeForm.post = (args: { shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } } | [shareToken: string | number, asset: number | { id: number }, comment: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleAssetCommentLike.url(args, options),
    method: 'post',
})

toggleAssetCommentLike.form = toggleAssetCommentLikeForm

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
export const downloadPublicAsset = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'get',
})

downloadPublicAsset.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets/{asset}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return downloadPublicAsset.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
const downloadPublicAssetForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAssetForm.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:2067
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAssetForm.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

downloadPublicAsset.form = downloadPublicAssetForm

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:2085
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
export const deletePublicAsset = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deletePublicAsset.url(args, options),
    method: 'post',
})

deletePublicAsset.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:2085
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAsset.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return deletePublicAsset.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:2085
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAsset.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deletePublicAsset.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:2085
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
const deletePublicAssetForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deletePublicAsset.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:2085
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAssetForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deletePublicAsset.url(args, options),
    method: 'post',
})

deletePublicAsset.form = deletePublicAssetForm

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
export const wall = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

wall.definition = {
    methods: ["get","head"],
    url: '/wall/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
wall.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return wall.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
wall.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
wall.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wall.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
const wallForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
wallForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2107
* @route '/wall/{shareToken}'
*/
wallForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

wall.form = wallForm

const EventController = { show, guests, guestReport, storeGuestParty, importGuestParties, exportGuestLedger, bulkUpdateGuestInvitations, updateInvitationSettings, updateGuestParty, destroyGuestParty, media, startMediaExport, downloadMediaExport, bulkDestroyAssets, bulkUpdateAssetModeration, destroyAsset, updateAssetModeration, updateAssetWallVisibility, settings, updateSettings, updateBilling, createBillingCheckout, storeCollaborator, acceptCollaboratorInvite, completeCollaboratorInviteRegistration, completeCollaboratorInviteLogin, guestInvitation, respondToGuestInvitation, publicInvitation, respondToPublicInvitation, album, albumAssets, guestProfile, upsertGuestProfile, upload, postText, toggleAssetLike, assetComments, storeAssetComment, toggleAssetCommentLike, downloadPublicAsset, deletePublicAsset, wall }

export default EventController