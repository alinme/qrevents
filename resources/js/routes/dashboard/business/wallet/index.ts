import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:92
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
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
checkout.url = (options?: RouteQueryOptions) => {
    return checkout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
checkout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
const checkoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::checkout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
checkoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(options),
    method: 'post',
})

checkout.form = checkoutForm

const wallet = {
    checkout: Object.assign(checkout, checkout),
}

export default wallet