<?php namespace Winter\SiteSearch\Components;

use Cms\Classes\ComponentBase;

class SiteSearchInclude extends ComponentBase
{
    /**
     * This is a simple empty dummy component to mark
     * cms pages that should be included in the
     * search results.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'winter.sitesearch::lang.siteSearchInclude.title',
            'description' => 'winter.sitesearch::lang.siteSearchInclude.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }
}
