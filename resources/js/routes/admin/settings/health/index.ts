import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
export const data = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: data.url(options),
    method: 'get',
})

data.definition = {
    methods: ["get","head"],
    url: '/admin/settings/health/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
data.url = (options?: RouteQueryOptions) => {
    return data.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
data.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: data.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
data.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: data.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
    const dataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: data.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
        dataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: data.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::data
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
        dataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: data.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    data.form = dataForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
export const retryFailed = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryFailed.url(options),
    method: 'post',
})

retryFailed.definition = {
    methods: ["post"],
    url: '/admin/settings/health/retry-failed',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
retryFailed.url = (options?: RouteQueryOptions) => {
    return retryFailed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
retryFailed.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryFailed.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
    const retryFailedForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: retryFailed.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
        retryFailedForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: retryFailed.url(options),
            method: 'post',
        })
    
    retryFailed.form = retryFailedForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
export const flushFailed = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: flushFailed.url(options),
    method: 'post',
})

flushFailed.definition = {
    methods: ["post"],
    url: '/admin/settings/health/flush-failed',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
flushFailed.url = (options?: RouteQueryOptions) => {
    return flushFailed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
flushFailed.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: flushFailed.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
    const flushFailedForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: flushFailed.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailed
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
        flushFailedForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: flushFailed.url(options),
            method: 'post',
        })
    
    flushFailed.form = flushFailedForm
const health = {
    data: Object.assign(data, data),
retryFailed: Object.assign(retryFailed, retryFailed),
flushFailed: Object.assign(flushFailed, flushFailed),
}

export default health