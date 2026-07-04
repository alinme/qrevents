import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
export const show = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/users/{user}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
show.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return show.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
show.get = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
show.head = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
    const showForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
        showForm.get = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:64
 * @route '/admin/users/{user}'
 */
        showForm.head = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\UserController::accountType
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/{user}/account-type'
 */
export const accountType = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: accountType.url(args, options),
    method: 'patch',
})

accountType.definition = {
    methods: ["patch"],
    url: '/admin/users/{user}/account-type',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\UserController::accountType
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/{user}/account-type'
 */
accountType.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return accountType.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::accountType
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/{user}/account-type'
 */
accountType.patch = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: accountType.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::accountType
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/{user}/account-type'
 */
    const accountTypeForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: accountType.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::accountType
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/{user}/account-type'
 */
        accountTypeForm.patch = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: accountType.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    accountType.form = accountTypeForm
/**
* @see \App\Http\Controllers\Admin\UserController::verify
 * @see app/Http/Controllers/Admin/UserController.php:177
 * @route '/admin/users/{user}/verify'
 */
export const verify = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(args, options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/admin/users/{user}/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::verify
 * @see app/Http/Controllers/Admin/UserController.php:177
 * @route '/admin/users/{user}/verify'
 */
verify.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return verify.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::verify
 * @see app/Http/Controllers/Admin/UserController.php:177
 * @route '/admin/users/{user}/verify'
 */
verify.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::verify
 * @see app/Http/Controllers/Admin/UserController.php:177
 * @route '/admin/users/{user}/verify'
 */
    const verifyForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::verify
 * @see app/Http/Controllers/Admin/UserController.php:177
 * @route '/admin/users/{user}/verify'
 */
        verifyForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(args, options),
            method: 'post',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\Admin\UserController::sendReset
 * @see app/Http/Controllers/Admin/UserController.php:153
 * @route '/admin/users/{user}/send-reset'
 */
export const sendReset = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendReset.url(args, options),
    method: 'post',
})

sendReset.definition = {
    methods: ["post"],
    url: '/admin/users/{user}/send-reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::sendReset
 * @see app/Http/Controllers/Admin/UserController.php:153
 * @route '/admin/users/{user}/send-reset'
 */
sendReset.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return sendReset.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::sendReset
 * @see app/Http/Controllers/Admin/UserController.php:153
 * @route '/admin/users/{user}/send-reset'
 */
sendReset.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendReset.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::sendReset
 * @see app/Http/Controllers/Admin/UserController.php:153
 * @route '/admin/users/{user}/send-reset'
 */
    const sendResetForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendReset.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::sendReset
 * @see app/Http/Controllers/Admin/UserController.php:153
 * @route '/admin/users/{user}/send-reset'
 */
        sendResetForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendReset.url(args, options),
            method: 'post',
        })
    
    sendReset.form = sendResetForm
/**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:199
 * @route '/admin/users/{user}'
 */
export const destroy = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/users/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:199
 * @route '/admin/users/{user}'
 */
destroy.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return destroy.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:199
 * @route '/admin/users/{user}'
 */
destroy.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:199
 * @route '/admin/users/{user}'
 */
    const destroyForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:199
 * @route '/admin/users/{user}'
 */
        destroyForm.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const users = {
    show: Object.assign(show, show),
accountType: Object.assign(accountType, accountType),
verify: Object.assign(verify, verify),
sendReset: Object.assign(sendReset, sendReset),
destroy: Object.assign(destroy, destroy),
}

export default users