import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import general5d934c from './general'
import email85aaee from './email'
import seoC41c0c from './seo'
import integrations3d3f6e from './integrations'
import healthC4695b from './health'
import devtoolsE8467b from './devtools'
/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
export const general = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: general.url(options),
    method: 'get',
})

general.definition = {
    methods: ["get","head"],
    url: '/admin/settings/general',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
general.url = (options?: RouteQueryOptions) => {
    return general.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
general.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: general.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
general.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: general.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
    const generalForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: general.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
        generalForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: general.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:30
 * @route '/admin/settings/general'
 */
        generalForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: general.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    general.form = generalForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
export const email = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: email.url(options),
    method: 'get',
})

email.definition = {
    methods: ["get","head"],
    url: '/admin/settings/email',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
email.url = (options?: RouteQueryOptions) => {
    return email.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
email.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: email.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
email.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: email.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
    const emailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: email.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
        emailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: email.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:82
 * @route '/admin/settings/email'
 */
        emailForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: email.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    email.form = emailForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
export const seo = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seo.url(options),
    method: 'get',
})

seo.definition = {
    methods: ["get","head"],
    url: '/admin/settings/seo',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
seo.url = (options?: RouteQueryOptions) => {
    return seo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
seo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seo.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
seo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: seo.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
    const seoForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: seo.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
        seoForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seo.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:152
 * @route '/admin/settings/seo'
 */
        seoForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seo.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    seo.form = seoForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
export const integrations = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: integrations.url(options),
    method: 'get',
})

integrations.definition = {
    methods: ["get","head"],
    url: '/admin/settings/integrations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
integrations.url = (options?: RouteQueryOptions) => {
    return integrations.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
integrations.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: integrations.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
integrations.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: integrations.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
    const integrationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: integrations.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
        integrationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: integrations.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:194
 * @route '/admin/settings/integrations'
 */
        integrationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: integrations.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    integrations.form = integrationsForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
export const health = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: health.url(options),
    method: 'get',
})

health.definition = {
    methods: ["get","head"],
    url: '/admin/settings/health',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
health.url = (options?: RouteQueryOptions) => {
    return health.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
health.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: health.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
health.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: health.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
    const healthForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: health.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
        healthForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: health.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:256
 * @route '/admin/settings/health'
 */
        healthForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: health.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    health.form = healthForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
export const devtools = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: devtools.url(options),
    method: 'get',
})

devtools.definition = {
    methods: ["get","head"],
    url: '/admin/settings/devtools',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
devtools.url = (options?: RouteQueryOptions) => {
    return devtools.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
devtools.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: devtools.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
devtools.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: devtools.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
    const devtoolsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: devtools.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
        devtoolsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: devtools.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:299
 * @route '/admin/settings/devtools'
 */
        devtoolsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: devtools.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    devtools.form = devtoolsForm
const settings = {
    general: Object.assign(general, general5d934c),
email: Object.assign(email, email85aaee),
seo: Object.assign(seo, seoC41c0c),
integrations: Object.assign(integrations, integrations3d3f6e),
health: Object.assign(health, healthC4695b),
devtools: Object.assign(devtools, devtoolsE8467b),
}

export default settings