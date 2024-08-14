import http from 'k6/http';
import { check } from 'k6';

export const getHost = () => {
    const environment = __ENV.APP_ENV || 'local';
    switch (environment) {
        case 'production':
        case 'prod':
            return 'https://api.cyberriskalliance.com';
        case 'qa1':
        case 'qa2':
        case 'preprod':
            return `https://api-${environment}.cyberriskalliance.com`;
        default:
            return 'https://blemmyae.ddev.site';
    }
}

export const getGraphqlUrl = () => `${getHost()}/graphql`;

const url = getGraphqlUrl();

export const performQuery = (query, variables) => {
    const res = http.post(url, JSON.stringify({query, variables}), {
        headers: {
            "Content-Type": "application/json",
            "x-preview-token": "bypass cache"
        }
    });
    check(res, {
        'is status 200': (r) => r.status === 200,
    });

    let body = null;
    try {
        body = JSON.parse(res.body);
    } catch (e) {
        // Intentionally blank. It's always a 503 error from varnish in case of an error here.
    }
    check(body, {'is body JSON': (body) => body !== null})

    return body || {};
}

export const openQuery = (name) => open(`./queries/${name}.graphql`)
