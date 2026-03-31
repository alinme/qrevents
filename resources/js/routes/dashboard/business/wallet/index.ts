import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/wallet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::history
* @see app/Http/Controllers/DashboardController.php:172
* @route '/dashboard/business/wallet'
*/
historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

history.form = historyForm

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:152
* @route '/dashboard/business/wallet/checkout'
*/
export const checkout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(options),
    method: 'post',
})

checkout.definition = {
    methods: ["post"],
    url: '/dashboard/business/wallet/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:152
* @route '/dashboard/business/wallet/checkout'
*/
checkout.url = (options?: RouteQueryOptions) => {
    return checkout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:152
* @route '/dashboard/business/wallet/checkout'
*/
checkout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:152
* @route '/dashboard/business/wallet/checkout'
*/
const checkoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:152
* @route '/dashboard/business/wallet/checkout'
*/
checkoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(options),
    method: 'post',
})

checkout.form = checkoutForm

const wallet = {
    history: Object.assign(history, history),
    checkout: Object.assign(checkout, checkout),
}

export default wallet