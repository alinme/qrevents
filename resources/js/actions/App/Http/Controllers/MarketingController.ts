import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
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
* @see app/Http/Controllers/MarketingController.php:15
* @route '/pricing'
*/
pricing.url = (options?: RouteQueryOptions) => {
    return pricing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
* @route '/pricing'
*/
pricing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
* @route '/pricing'
*/
pricing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pricing.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
* @route '/pricing'
*/
const pricingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
* @route '/pricing'
*/
pricingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pricing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::pricing
* @see app/Http/Controllers/MarketingController.php:15
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

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
export const businesses = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: businesses.url(options),
    method: 'get',
})

businesses.definition = {
    methods: ["get","head"],
    url: '/businesses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
businesses.url = (options?: RouteQueryOptions) => {
    return businesses.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
businesses.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: businesses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
businesses.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: businesses.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
const businessesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: businesses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
businessesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: businesses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MarketingController::businesses
* @see app/Http/Controllers/MarketingController.php:26
* @route '/businesses'
*/
businessesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: businesses.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

businesses.form = businessesForm

const MarketingController = { pricing, businesses }

export default MarketingController