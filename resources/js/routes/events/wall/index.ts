import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
export const legacy = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: legacy.url(args, options),
    method: 'get',
})

legacy.definition = {
    methods: ["get","head"],
    url: '/wall/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
legacy.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return legacy.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
legacy.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: legacy.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
legacy.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: legacy.url(args, options),
    method: 'head',
})

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
const legacyForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: legacy.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
legacyForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: legacy.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:183
* @route '/wall/{shareToken}'
*/
legacyForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: legacy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

legacy.form = legacyForm

const wall = {
    legacy: Object.assign(legacy, legacy),
}

export default wall