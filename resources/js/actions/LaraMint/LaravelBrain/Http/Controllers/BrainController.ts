import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../../../../../wayfinder'
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
export const source = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: source.url(options),
    method: 'get',
})

source.definition = {
    methods: ["get","head"],
    url: '/_laravel-brain/api/source',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
source.url = (options?: RouteQueryOptions) => {
    return source.definition.url + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
source.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: source.url(options),
    method: 'get',
})
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
source.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: source.url(options),
    method: 'head',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
    const sourceForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: source.url(options),
        method: 'get',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
        sourceForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: source.url(options),
            method: 'get',
        })
            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::source
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:19
 * @route '/_laravel-brain/api/source'
 */
        sourceForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: source.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    source.form = sourceForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::scan
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:32
 * @route '/_laravel-brain/api/scan'
 */
export const scan = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: scan.url(options),
    method: 'post',
})

scan.definition = {
    methods: ["post"],
    url: '/_laravel-brain/api/scan',
} satisfies RouteDefinition<["post"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::scan
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:32
 * @route '/_laravel-brain/api/scan'
 */
scan.url = (options?: RouteQueryOptions) => {
    return scan.definition.url + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::scan
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:32
 * @route '/_laravel-brain/api/scan'
 */
scan.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: scan.url(options),
    method: 'post',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::scan
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:32
 * @route '/_laravel-brain/api/scan'
 */
    const scanForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: scan.url(options),
        method: 'post',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::scan
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:32
 * @route '/_laravel-brain/api/scan'
 */
        scanForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: scan.url(options),
            method: 'post',
        })
    
    scan.form = scanForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTest
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:170
 * @route '/_laravel-brain/api/stress-test'
 */
export const stressTest = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stressTest.url(options),
    method: 'post',
})

stressTest.definition = {
    methods: ["post"],
    url: '/_laravel-brain/api/stress-test',
} satisfies RouteDefinition<["post"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTest
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:170
 * @route '/_laravel-brain/api/stress-test'
 */
stressTest.url = (options?: RouteQueryOptions) => {
    return stressTest.definition.url + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTest
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:170
 * @route '/_laravel-brain/api/stress-test'
 */
stressTest.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stressTest.url(options),
    method: 'post',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTest
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:170
 * @route '/_laravel-brain/api/stress-test'
 */
    const stressTestForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: stressTest.url(options),
        method: 'post',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTest
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:170
 * @route '/_laravel-brain/api/stress-test'
 */
        stressTestForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: stressTest.url(options),
            method: 'post',
        })
    
    stressTest.form = stressTestForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
export const stressTestPoll = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stressTestPoll.url(args, options),
    method: 'get',
})

stressTestPoll.definition = {
    methods: ["get","head"],
    url: '/_laravel-brain/api/stress-test/{jobId}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
stressTestPoll.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jobId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    jobId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        jobId: args.jobId,
                }

    return stressTestPoll.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
stressTestPoll.get = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stressTestPoll.url(args, options),
    method: 'get',
})
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
stressTestPoll.head = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stressTestPoll.url(args, options),
    method: 'head',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
    const stressTestPollForm = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stressTestPoll.url(args, options),
        method: 'get',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
        stressTestPollForm.get = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stressTestPoll.url(args, options),
            method: 'get',
        })
            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::stressTestPoll
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:246
 * @route '/_laravel-brain/api/stress-test/{jobId}'
 */
        stressTestPollForm.head = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stressTestPoll.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stressTestPoll.form = stressTestPollForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
export const context = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: context.url(options),
    method: 'get',
})

context.definition = {
    methods: ["get","head"],
    url: '/_laravel-brain/api/context',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
context.url = (options?: RouteQueryOptions) => {
    return context.definition.url + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
context.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: context.url(options),
    method: 'get',
})
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
context.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: context.url(options),
    method: 'head',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
    const contextForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: context.url(options),
        method: 'get',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
        contextForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: context.url(options),
            method: 'get',
        })
            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::context
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:65
 * @route '/_laravel-brain/api/context'
 */
        contextForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: context.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    context.form = contextForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::generateRules
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:106
 * @route '/_laravel-brain/api/generate-rules'
 */
export const generateRules = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateRules.url(options),
    method: 'post',
})

generateRules.definition = {
    methods: ["post"],
    url: '/_laravel-brain/api/generate-rules',
} satisfies RouteDefinition<["post"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::generateRules
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:106
 * @route '/_laravel-brain/api/generate-rules'
 */
generateRules.url = (options?: RouteQueryOptions) => {
    return generateRules.definition.url + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::generateRules
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:106
 * @route '/_laravel-brain/api/generate-rules'
 */
generateRules.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateRules.url(options),
    method: 'post',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::generateRules
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:106
 * @route '/_laravel-brain/api/generate-rules'
 */
    const generateRulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generateRules.url(options),
        method: 'post',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::generateRules
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:106
 * @route '/_laravel-brain/api/generate-rules'
 */
        generateRulesForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generateRules.url(options),
            method: 'post',
        })
    
    generateRules.form = generateRulesForm
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
export const serve = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serve.url(args, options),
    method: 'get',
})

serve.definition = {
    methods: ["get","head"],
    url: '/_laravel-brain/{any?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
serve.url = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { any: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    any: args[0],
                }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
            "any",
        ])

    const parsedArgs = {
                        any: args?.any,
                }

    return serve.definition.url
            .replace('{any?}', parsedArgs.any?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
serve.get = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serve.url(args, options),
    method: 'get',
})
/**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
serve.head = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: serve.url(args, options),
    method: 'head',
})

    /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
    const serveForm = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: serve.url(args, options),
        method: 'get',
    })

            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
        serveForm.get = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serve.url(args, options),
            method: 'get',
        })
            /**
* @see \LaraMint\LaravelBrain\Http\Controllers\BrainController::serve
 * @see vendor/laramint/laravel-brain/src/Http/Controllers/BrainController.php:267
 * @route '/_laravel-brain/{any?}'
 */
        serveForm.head = (args?: { any?: string | number } | [any: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serve.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    serve.form = serveForm
const BrainController = { source, scan, stressTest, stressTestPoll, context, generateRules, serve }

export default BrainController