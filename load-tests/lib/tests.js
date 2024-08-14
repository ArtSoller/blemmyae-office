import { check } from 'k6';
import { performQuery, openQuery } from './base.js';

// EDITORIAL

const firstPostSlugQuery = openQuery('firstPostSlug')
let firstEditorialSlug;

const editorialContentQuery = openQuery('editorialContent');

export const checkEditorial = () => {
    if (!firstEditorialSlug) {
        firstEditorialSlug = performQuery(firstPostSlugQuery, {
            applications: ['scm'],
            postType: ['EDITORIAL']
        }).data.contentTeaserPosts.contentTeaserPostsConnection.nodes[0].slug;
    }

    const body = performQuery(editorialContentQuery, {
        id: firstEditorialSlug,
        applications: ["scm"]
    });
    check(body, {
        'contains non-empty editorialWithRelatedBlock': (body) => !!(body.data && body.data.editorialWithRelatedBlock),
        'no errors': (body) => !body.errors,
    });
}

// LANDING

const landingWithBlocksQuery = openQuery('landingWithBlocks');

export const checkLanding = () => {
    const body = performQuery(landingWithBlocksQuery, {
        id: "homepage",
        applications: ["scm"]
    });
    check(body, {
        'contains non-empty landingBySlug': (body) => !!(body.data && body.data.landingBySlug),
        'no errors': (body) => !body.errors,
    });
}

// TYPENAME

const typenameQuery = openQuery('typename');

export const checkTypename = () => {
    const body = performQuery(typenameQuery, {});
    check(body, {
        'RootQuery __typename': (body) => body.data && body.data.__typename === 'RootQuery',
    })
}
