import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import exports from './exports'
/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
export const billingQueue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billingQueue.url(options),
    method: 'get',
})

billingQueue.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/actions/billing-queue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.url = (options?: RouteQueryOptions) => {
    return billingQueue.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: billingQueue.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
const billingQueueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:194
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

billingQueue.form = billingQueueForm

const business = {
    exports: Object.assign(exports, exports),
    billingQueue: Object.assign(billingQueue, billingQueue),
}

export default business