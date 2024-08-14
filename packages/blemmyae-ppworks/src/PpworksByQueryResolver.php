<?php

namespace Cra\BlemmyaePpworks;

use Scm\Tools\PostByQueryResolver;

class PpworksByQueryResolver extends PostByQueryResolver
{
    /**
     * Adds actions to register graphql types and fields
     */
    public function __construct($postType)
    {
        $this->postType = $postType;
        $this->ctName = match ($postType) {
            PpworksAnnouncementCT::POST_TYPE => "PpworksAnnouncement",
            PpworksArticleCT::POST_TYPE => "PPWorksArticle",
            PpworksEpisodeCT::POST_TYPE => "PpworksEpisode",
            PpworksSegmentCT::POST_TYPE => "PpworksSegment",
            PpworksSponsorProgramCT::POST_TYPE => "PPWorksSponsorProgram",
            default => ucfirst($postType),
        };
        $this->ctDomain = 'blemmyae-ppworks';
        $this->registerFields();
    }
}
