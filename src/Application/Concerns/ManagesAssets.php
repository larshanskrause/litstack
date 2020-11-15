<?php

namespace Ignite\Application\Concerns;

use Ignite\Support\Facades\Route;

trait ManagesAssets
{
    /**
     * Included css files..
     *
     * @var array
     */
    protected $styles = [];

    /**
     * Included scripts.
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * Add script to the application.
     *
     * @param  string $src
     * @return $this
     */
    public function script($src)
    {
        if (in_array($src, $this->scripts)) {
            return;
        }

        $this->scripts[] = $this->resolveAssetPath($src);

        return $this;
    }

    /**
     * Add css file to the application.
     *
     * @param  string $path
     * @return $this
     */
    public function style($path)
    {
        if (in_array($path, $this->styles)) {
            return;
        }

        $this->styles[] = $this->resolveAssetPath($path);

        return $this;
    }

    /**
     * Get styles.
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Get scripts.
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Resolve path to asset.
     *
     * @param  string $path
     * @return void
     */
    protected function resolveAssetPath($path)
    {
        if (! file_exists($path)) {
            return $path;
        }

        $info = pathinfo($path);

        $uri = implode('/', [
            $info['extension'],
            $info['basename'],
        ]);

        $route = Route::public()->get($uri, function () use ($path, $info) {
            return response(app('files')->get($path), 200)
                ->header('Content-Type', [
                    'js'  => 'application/javascript; charset=utf-8',
                    'css' => 'text/css',
                ][$info['extension']] ?? 'text/'.$info['extension']);
        });

        return url($route->uri);
    }
}
