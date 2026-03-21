import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
export const pricing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing.url(options),
    method: 'get',
})

pricing.definition = {
    methods: ["get","head"],
    url: '/pricing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
pricing.url = (options?: RouteQueryOptions) => {
    return pricing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
pricing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
pricing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pricing.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
const pricingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
pricingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:12
* @route '/pricing'
*/
pricingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pricing.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

pricing.form = pricingForm

const MarketingController = { pricing }

export default MarketingController