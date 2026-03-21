import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:38
* @route '/dashboard'
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
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
export const account = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})

account.definition = {
    methods: ["get","head"],
    url: '/dashboard/account',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
account.url = (options?: RouteQueryOptions) => {
    return account.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
account.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
account.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: account.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
const accountForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
accountForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::account
* @see app/Http/Controllers/DashboardController.php:51
* @route '/dashboard/account'
*/
accountForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: account.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

account.form = accountForm

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
export const business = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

business.definition = {
    methods: ["get","head"],
    url: '/dashboard/business',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
business.url = (options?: RouteQueryOptions) => {
    return business.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
business.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
business.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: business.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
const businessForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
businessForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::business
* @see app/Http/Controllers/DashboardController.php:70
* @route '/dashboard/business'
*/
businessForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

business.form = businessForm

/**
* @see \App\Http\Controllers\DashboardController::startFilteredExports
* @see app/Http/Controllers/DashboardController.php:114
* @route '/dashboard/business/actions/start-exports'
*/
export const startFilteredExports = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: startFilteredExports.url(options),
    method: 'post',
})

startFilteredExports.definition = {
    methods: ["post"],
    url: '/dashboard/business/actions/start-exports',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::startFilteredExports
* @see app/Http/Controllers/DashboardController.php:114
* @route '/dashboard/business/actions/start-exports'
*/
startFilteredExports.url = (options?: RouteQueryOptions) => {
    return startFilteredExports.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::startFilteredExports
* @see app/Http/Controllers/DashboardController.php:114
* @route '/dashboard/business/actions/start-exports'
*/
startFilteredExports.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: startFilteredExports.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardController::startFilteredExports
* @see app/Http/Controllers/DashboardController.php:114
* @route '/dashboard/business/actions/start-exports'
*/
const startFilteredExportsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: startFilteredExports.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardController::startFilteredExports
* @see app/Http/Controllers/DashboardController.php:114
* @route '/dashboard/business/actions/start-exports'
*/
startFilteredExportsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: startFilteredExports.url(options),
    method: 'post',
})

startFilteredExports.form = startFilteredExportsForm

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
export const downloadBillingQueue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadBillingQueue.url(options),
    method: 'get',
})

downloadBillingQueue.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/actions/billing-queue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
downloadBillingQueue.url = (options?: RouteQueryOptions) => {
    return downloadBillingQueue.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
downloadBillingQueue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadBillingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
downloadBillingQueue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadBillingQueue.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
const downloadBillingQueueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadBillingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
downloadBillingQueueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadBillingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::downloadBillingQueue
* @see app/Http/Controllers/DashboardController.php:190
* @route '/dashboard/business/actions/billing-queue'
*/
downloadBillingQueueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadBillingQueue.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

downloadBillingQueue.form = downloadBillingQueueForm

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
export const ownedEvents = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ownedEvents.url(options),
    method: 'get',
})

ownedEvents.definition = {
    methods: ["get","head"],
    url: '/dashboard/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
ownedEvents.url = (options?: RouteQueryOptions) => {
    return ownedEvents.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
ownedEvents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
ownedEvents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ownedEvents.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
const ownedEventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
ownedEventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ownedEvents
* @see app/Http/Controllers/DashboardController.php:248
* @route '/dashboard/events'
*/
ownedEventsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ownedEvents.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ownedEvents.form = ownedEventsForm

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
export const recentActivity = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentActivity.url(options),
    method: 'get',
})

recentActivity.definition = {
    methods: ["get","head"],
    url: '/dashboard/activity',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
recentActivity.url = (options?: RouteQueryOptions) => {
    return recentActivity.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
recentActivity.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
recentActivity.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recentActivity.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
const recentActivityForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
recentActivityForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::recentActivity
* @see app/Http/Controllers/DashboardController.php:260
* @route '/dashboard/activity'
*/
recentActivityForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: recentActivity.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

recentActivity.form = recentActivityForm

const DashboardController = { index, account, business, startFilteredExports, downloadBillingQueue, ownedEvents, recentActivity }

export default DashboardController