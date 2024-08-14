import {registerBlockType} from '@wordpress/blocks';

const unsupportedBlockTypes = [
    'fl-builder/layout', // SW
    'haymarket/column',
    'haymarket/column-layout',
    'haymarket/content',
    'haymarket/related-articles',
    'haymarket/ad',
    'haymarket/related-links',
    'haymarket/html-asset',
    'haymarket/group-test',
    'haymarket/review',
    'haymarket/webcast',
    'themify-builder/canvas', // SW
];

const iterator = unsupportedBlockTypes.values();

for (const value of iterator) {
    console.log(value);
    const output = '<!-- Missing support for gutenberg block type`' + value + '` -->';
    registerBlockType(value, {
        apiVersion: 2,
        title: 'Unsupported block type: ' + value,
        icon: 'dashicons-warning',
        category: 'design',
        example: {},
        edit() {
            return output;
        },
        save() {
            return output;
        },
    });
}
