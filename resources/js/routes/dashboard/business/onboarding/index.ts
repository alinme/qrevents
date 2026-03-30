import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessController::cancel
* @see app/Http/Controllers/BusinessController.php:70
* @route '/dashboard/business/onboarding/cancel'
*/
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/dashboard/business/onboarding/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::cancel
* @see app/Http/Controllers/BusinessController.php:70
* @route '/dashboard/business/onboarding/cancel'
*/
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::cancel
* @see app/Http/Controllers/BusinessController.php:70
* @route '/dashboard/business/onboarding/cancel'
*/
cancel.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::cancel
* @see app/Http/Controllers/BusinessController.php:70
* @route '/dashboard/business/onboarding/cancel'
*/
const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cancel.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::cancel
* @see app/Http/Controllers/BusinessController.php:70
* @route '/dashboard/business/onboarding/cancel'
*/
cancelForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cancel.url(options),
    method: 'post',
})

cancel.form = cancelForm

/**
* @see \App\Http\Controllers\BusinessController::store
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/onboarding'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/business/onboarding',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::store
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/onboarding'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::store
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/onboarding'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::store
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/onboarding'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::store
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/onboarding'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const onboarding = {
    cancel: Object.assign(cancel, cancel),
    store: Object.assign(store, store),
}

export default onboarding