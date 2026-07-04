import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
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
 * @see app/Http/Controllers/Admin/SettingsController.php:32
 * @route '/admin/settings/general'
 */
general.url = (options?: RouteQueryOptions) => {
    return general.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
 * @route '/admin/settings/general'
 */
general.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: general.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
 * @route '/admin/settings/general'
 */
general.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: general.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
 * @route '/admin/settings/general'
 */
    const generalForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: general.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
 * @route '/admin/settings/general'
 */
        generalForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: general.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::general
 * @see app/Http/Controllers/Admin/SettingsController.php:32
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
* @see \App\Http\Controllers\Admin\SettingsController::updateGeneral
 * @see app/Http/Controllers/Admin/SettingsController.php:49
 * @route '/admin/settings/general'
 */
export const updateGeneral = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateGeneral.url(options),
    method: 'put',
})

updateGeneral.definition = {
    methods: ["put"],
    url: '/admin/settings/general',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateGeneral
 * @see app/Http/Controllers/Admin/SettingsController.php:49
 * @route '/admin/settings/general'
 */
updateGeneral.url = (options?: RouteQueryOptions) => {
    return updateGeneral.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateGeneral
 * @see app/Http/Controllers/Admin/SettingsController.php:49
 * @route '/admin/settings/general'
 */
updateGeneral.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateGeneral.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::updateGeneral
 * @see app/Http/Controllers/Admin/SettingsController.php:49
 * @route '/admin/settings/general'
 */
    const updateGeneralForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateGeneral.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::updateGeneral
 * @see app/Http/Controllers/Admin/SettingsController.php:49
 * @route '/admin/settings/general'
 */
        updateGeneralForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateGeneral.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateGeneral.form = updateGeneralForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
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
 * @see app/Http/Controllers/Admin/SettingsController.php:94
 * @route '/admin/settings/email'
 */
email.url = (options?: RouteQueryOptions) => {
    return email.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
 * @route '/admin/settings/email'
 */
email.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: email.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
 * @route '/admin/settings/email'
 */
email.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: email.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
 * @route '/admin/settings/email'
 */
    const emailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: email.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
 * @route '/admin/settings/email'
 */
        emailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: email.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::email
 * @see app/Http/Controllers/Admin/SettingsController.php:94
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
* @see \App\Http\Controllers\Admin\SettingsController::updateEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
export const updateEmail = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateEmail.url(options),
    method: 'put',
})

updateEmail.definition = {
    methods: ["put"],
    url: '/admin/settings/email',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
updateEmail.url = (options?: RouteQueryOptions) => {
    return updateEmail.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
updateEmail.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateEmail.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::updateEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
    const updateEmailForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateEmail.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::updateEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
        updateEmailForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateEmail.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateEmail.form = updateEmailForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::testEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
export const testEmail = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testEmail.url(options),
    method: 'post',
})

testEmail.definition = {
    methods: ["post"],
    url: '/admin/settings/email/test',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::testEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
testEmail.url = (options?: RouteQueryOptions) => {
    return testEmail.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::testEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
testEmail.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testEmail.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::testEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
    const testEmailForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: testEmail.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::testEmail
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
        testEmailForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: testEmail.url(options),
            method: 'post',
        })
    
    testEmail.form = testEmailForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
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
 * @see app/Http/Controllers/Admin/SettingsController.php:164
 * @route '/admin/settings/seo'
 */
seo.url = (options?: RouteQueryOptions) => {
    return seo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
 * @route '/admin/settings/seo'
 */
seo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seo.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
 * @route '/admin/settings/seo'
 */
seo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: seo.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
 * @route '/admin/settings/seo'
 */
    const seoForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: seo.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
 * @route '/admin/settings/seo'
 */
        seoForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seo.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::seo
 * @see app/Http/Controllers/Admin/SettingsController.php:164
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
* @see \App\Http\Controllers\Admin\SettingsController::updateSeo
 * @see app/Http/Controllers/Admin/SettingsController.php:192
 * @route '/admin/settings/seo'
 */
export const updateSeo = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSeo.url(options),
    method: 'put',
})

updateSeo.definition = {
    methods: ["put"],
    url: '/admin/settings/seo',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateSeo
 * @see app/Http/Controllers/Admin/SettingsController.php:192
 * @route '/admin/settings/seo'
 */
updateSeo.url = (options?: RouteQueryOptions) => {
    return updateSeo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateSeo
 * @see app/Http/Controllers/Admin/SettingsController.php:192
 * @route '/admin/settings/seo'
 */
updateSeo.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSeo.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::updateSeo
 * @see app/Http/Controllers/Admin/SettingsController.php:192
 * @route '/admin/settings/seo'
 */
    const updateSeoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateSeo.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::updateSeo
 * @see app/Http/Controllers/Admin/SettingsController.php:192
 * @route '/admin/settings/seo'
 */
        updateSeoForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateSeo.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateSeo.form = updateSeoForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
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
 * @see app/Http/Controllers/Admin/SettingsController.php:231
 * @route '/admin/settings/integrations'
 */
integrations.url = (options?: RouteQueryOptions) => {
    return integrations.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
 * @route '/admin/settings/integrations'
 */
integrations.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: integrations.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
 * @route '/admin/settings/integrations'
 */
integrations.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: integrations.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
 * @route '/admin/settings/integrations'
 */
    const integrationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: integrations.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
 * @route '/admin/settings/integrations'
 */
        integrationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: integrations.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::integrations
 * @see app/Http/Controllers/Admin/SettingsController.php:231
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
* @see \App\Http\Controllers\Admin\SettingsController::updateIntegrations
 * @see app/Http/Controllers/Admin/SettingsController.php:246
 * @route '/admin/settings/integrations'
 */
export const updateIntegrations = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateIntegrations.url(options),
    method: 'put',
})

updateIntegrations.definition = {
    methods: ["put"],
    url: '/admin/settings/integrations',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateIntegrations
 * @see app/Http/Controllers/Admin/SettingsController.php:246
 * @route '/admin/settings/integrations'
 */
updateIntegrations.url = (options?: RouteQueryOptions) => {
    return updateIntegrations.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::updateIntegrations
 * @see app/Http/Controllers/Admin/SettingsController.php:246
 * @route '/admin/settings/integrations'
 */
updateIntegrations.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateIntegrations.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::updateIntegrations
 * @see app/Http/Controllers/Admin/SettingsController.php:246
 * @route '/admin/settings/integrations'
 */
    const updateIntegrationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateIntegrations.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::updateIntegrations
 * @see app/Http/Controllers/Admin/SettingsController.php:246
 * @route '/admin/settings/integrations'
 */
        updateIntegrationsForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateIntegrations.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateIntegrations.form = updateIntegrationsForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
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
 * @see app/Http/Controllers/Admin/SettingsController.php:273
 * @route '/admin/settings/health'
 */
health.url = (options?: RouteQueryOptions) => {
    return health.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
 * @route '/admin/settings/health'
 */
health.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: health.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
 * @route '/admin/settings/health'
 */
health.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: health.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
 * @route '/admin/settings/health'
 */
    const healthForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: health.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
 * @route '/admin/settings/health'
 */
        healthForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: health.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::health
 * @see app/Http/Controllers/Admin/SettingsController.php:273
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
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
export const healthData = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: healthData.url(options),
    method: 'get',
})

healthData.definition = {
    methods: ["get","head"],
    url: '/admin/settings/health/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
healthData.url = (options?: RouteQueryOptions) => {
    return healthData.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
healthData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: healthData.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
healthData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: healthData.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
    const healthDataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: healthData.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
        healthDataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: healthData.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::healthData
 * @see app/Http/Controllers/Admin/SettingsController.php:287
 * @route '/admin/settings/health/data'
 */
        healthDataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: healthData.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    healthData.form = healthDataForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
export const retryFailedJobs = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryFailedJobs.url(options),
    method: 'post',
})

retryFailedJobs.definition = {
    methods: ["post"],
    url: '/admin/settings/health/retry-failed',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
retryFailedJobs.url = (options?: RouteQueryOptions) => {
    return retryFailedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
retryFailedJobs.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryFailedJobs.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
    const retryFailedJobsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: retryFailedJobs.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::retryFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:294
 * @route '/admin/settings/health/retry-failed'
 */
        retryFailedJobsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: retryFailedJobs.url(options),
            method: 'post',
        })
    
    retryFailedJobs.form = retryFailedJobsForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
export const flushFailedJobs = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: flushFailedJobs.url(options),
    method: 'post',
})

flushFailedJobs.definition = {
    methods: ["post"],
    url: '/admin/settings/health/flush-failed',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
flushFailedJobs.url = (options?: RouteQueryOptions) => {
    return flushFailedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
flushFailedJobs.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: flushFailedJobs.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
    const flushFailedJobsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: flushFailedJobs.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::flushFailedJobs
 * @see app/Http/Controllers/Admin/SettingsController.php:304
 * @route '/admin/settings/health/flush-failed'
 */
        flushFailedJobsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: flushFailedJobs.url(options),
            method: 'post',
        })
    
    flushFailedJobs.form = flushFailedJobsForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
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
 * @see app/Http/Controllers/Admin/SettingsController.php:316
 * @route '/admin/settings/devtools'
 */
devtools.url = (options?: RouteQueryOptions) => {
    return devtools.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
 * @route '/admin/settings/devtools'
 */
devtools.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: devtools.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
 * @route '/admin/settings/devtools'
 */
devtools.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: devtools.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
 * @route '/admin/settings/devtools'
 */
    const devtoolsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: devtools.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
 * @route '/admin/settings/devtools'
 */
        devtoolsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: devtools.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::devtools
 * @see app/Http/Controllers/Admin/SettingsController.php:316
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
/**
* @see \App\Http\Controllers\Admin\SettingsController::runDevtool
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
export const runDevtool = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: runDevtool.url(options),
    method: 'post',
})

runDevtool.definition = {
    methods: ["post"],
    url: '/admin/settings/devtools/run',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::runDevtool
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
runDevtool.url = (options?: RouteQueryOptions) => {
    return runDevtool.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::runDevtool
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
runDevtool.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: runDevtool.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::runDevtool
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
    const runDevtoolForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: runDevtool.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::runDevtool
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
        runDevtoolForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: runDevtool.url(options),
            method: 'post',
        })
    
    runDevtool.form = runDevtoolForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
export const backupSql = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: backupSql.url(options),
    method: 'get',
})

backupSql.definition = {
    methods: ["get","head"],
    url: '/admin/settings/devtools/sql-backup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backupSql.url = (options?: RouteQueryOptions) => {
    return backupSql.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backupSql.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: backupSql.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backupSql.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: backupSql.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
    const backupSqlForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: backupSql.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
        backupSqlForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: backupSql.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::backupSql
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
        backupSqlForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: backupSql.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    backupSql.form = backupSqlForm
const SettingsController = { general, updateGeneral, email, updateEmail, testEmail, seo, updateSeo, integrations, updateIntegrations, health, healthData, retryFailedJobs, flushFailedJobs, devtools, runDevtool, backupSql }

export default SettingsController