<?php

namespace Winter\SiteSearch\Classes\Providers;

use Cms\Classes\Controller;
use DB;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Winter\SiteSearch\Classes\Result;
use Winter\SiteSearch\Models\Settings;

/**
 * Searches the contents generated by the
 * Graker.PhotoAlbums plugin
 *
 * @package Winter\SiteSearch\Classes\Providers
 */
class GrakerPhotoAlbumsResultsProvider extends ResultsProvider
{
    /**
     * @var Controller to be used to form urls to search results
     */
    protected $controller;

    /**
     * ResultsProvider constructor.
     *
     * @param                         $query
     * @param \Cms\Classes\Controller $controller
     */
    public function __construct($query, Controller $controller)
    {
        parent::__construct($query);
        $this->controller = $controller;
    }

    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->albums() as $album) {
            $this->addSearchResult($album, 'album');
        }

        foreach ($this->photos() as $photo) {
            $this->addSearchResult($photo, 'photo');
        }

        return $this;
    }

    /**
     * Process search result (album or photo)
     *
     * @param Album|Photo $model
     * @param string      $type album or photo
     */
    protected function addSearchResult($model, $type)
    {
        // Make this result more relevant, if the query is found in the title
        $relevance = mb_stripos($model->title, $this->query) === false ? 1 : 2;

        $result        = new Result($this->query, $relevance);
        $result->title = $model->title;
        $result->text  = $model->description;
        $result->meta  = $model->created_at;
        $result->url   = $model->setUrl(Settings::get('graker_photoalbums_' . $type . '_page', ''), $this->controller);
        $result->model = $model;

        if ($type == 'album') {
            $result->thumb = $model->getImage();
        } else if ($type == 'photo') {
            $result->thumb = $model->image;
        }

        $this->addResult($result);
    }

    /**
     * Search for albums (match title and description)
     *
     * @return Collection
     */
    protected function albums()
    {
        return Album::orderBy('created_at', 'desc')
                    ->where(function ($query) {
                        $query->where('title', 'like', "%{$this->query}%")
                              ->orWhere('description', 'like', "%{$this->query}%");
                    })
                    ->with(['latestPhoto' => function ($query) {
                        $query->with('image');
                    }])
                    ->with(['front' => function ($query) {
                        $query->with('image');
                    }])
                    ->get();
    }

    /**
     * Search for photos (match title and description)
     *
     * @return Collection
     */
    protected function photos()
    {
        return Photo::orderBy('created_at', 'desc')
                    ->where(function ($query) {
                        $query->where('title', 'like', "%{$this->query}%")
                              ->orWhere('description', 'like', "%{$this->query}%");
                    })->get();
    }

    /**
     * Checks if the RainLab.Blog Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable($this->identifier)
            && Settings::get('graker_photoalbums_enabled', true);
    }

    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return Settings::get('graker_photoalbums_label', 'Photoalbums');
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'Graker.PhotoAlbums';
    }
}
