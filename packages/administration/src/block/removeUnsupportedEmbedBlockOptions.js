wp.domReady(() => {
    const allowedEmbedBlocks = [
        'vimeo',
        'youtube',
        'twitter',
        'soundcloud'
    ];

    wp.blocks.getBlockVariations('core/embed').forEach((blockVariation) => {
        if (!allowedEmbedBlocks.includes(blockVariation.name)) {
            wp.blocks.unregisterBlockVariation('core/embed', blockVariation.name);
            console.log(blockVariation.name, "embed block is removed")
        }
    });
});
