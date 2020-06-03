<?php

namespace Fjord\Crud;

use Fjord\Support\Facades\Fjord;
use Fjord\User\Models\FjordUser;
use Fjord\Support\Facades\Config;
use Fjord\Support\Facades\Package;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Crud singleton.
 * 
 * @see \Fjord\Support\Facades\Crud
 */
class Crud
{
    /**
     * Get config by model.
     *
     * @param string $model
     * @return void
     */
    public function config($model)
    {
        $configKey = "crud." . lcfirst(class_basename($model));

        if (Config::exists($configKey)) {
            return Config::get($configKey);
        }
    }

    /**
     * Make routes for Crud Model.
     *
     * @param string $prefix
     * @param string $model
     * @param string $controller
     * @return void
     */
    public function routes(string $prefix, string $model, $identifier = 'id')
    {
        Package::get('aw-studio/fjord')->route()->group(function () use ($prefix, $model, $identifier) {
            $config = $this->config($model);
            $tableName = (new $model)->getTable();

            RouteFacade::group([
                'config' => $config->getKey(),
                'prefix' => "$prefix",
                'as' => "crud.{$tableName}.",
            ], function () use ($prefix, $model, $identifier, $config, $tableName) {

                $this->makeCrudRoutes($config->controller, $identifier);
                $this->makeFieldRoutes($config->controller, $identifier);

                Package::get('aw-studio/fjord')->addNavPreset("crud.{$tableName}", [
                    'link' => Fjord::url($prefix),
                    'title' => ucfirst($config->names['plural']),
                    'authorize' => function (FjordUser $user) use ($config) {
                        return (new $config->controller)->authorize($user, 'read');
                    }
                ]);
            });
        });
    }

    /**
     * Make routes for Forms.
     *
     * @param string $prefix
     * @param string $collection
     * @param string $form
     * @return void
     */
    public function formRoutes(string $prefix, string $collection, string $form)
    {
        Package::get('aw-studio/fjord')->route()->group(function () use ($prefix, $collection, $form) {
            $config = Config::get("form.{$collection}.{$form}");
            $url = "{$prefix}/{$collection}/{$form}";

            RouteFacade::group([
                'config' => $config->getKey(),
                'prefix' => "$url",
                'as' => "form.{$collection}.{$form}.",
            ], function () use ($url, $config, $collection, $form) {

                //require fjord_path('src/Crud/routes.php');
                $this->makeFormRoutes($config->controller);
                $this->makeFieldRoutes($config->controller);

                // Nav preset.
                Package::get('aw-studio/fjord')->addNavPreset("form.{$collection}.{$form}", [
                    'link' => Fjord::url($url),
                    'title' => ucfirst($config->names['singular']),
                    'authorize' => function (FjordUser $user) use ($config) {
                        return (new $config->controller)->authorize($user, 'read');
                    }
                ]);
            });
        });
    }

    /**
     * Make form routes.
     *
     * @param string $controller
     * @return void
     */
    protected function makeFormRoutes(string $controller)
    {
        RouteFacade::get("/", [$controller, "edit"])->name('edit');
    }

    /**
     * Make Crud Model routes.
     *
     * @param string $controller
     * @param string $identifier
     * @return void
     */
    protected function makeCrudRoutes(string $controller, string $identifier)
    {
        RouteFacade::get("/", [$controller, "index"])->name('index');
        // TODO: Index table name.
        RouteFacade::post("/index", [$controller, "indexTable"])->name('index.items');
        RouteFacade::get("{{$identifier}}", [$controller, "edit"])->name('edit');
        RouteFacade::get("/create", [$controller, "create"])->name('create');
        RouteFacade::post("/", [$controller, "store"])->name('store');
        RouteFacade::delete("/{id}", [$controller, "destroy"])->name('destroy');
        RouteFacade::post("/delete-all", [$controller, "destroyAll"])->name('destroy.all');
        RouteFacade::post("/order", [$controller, "order"])->name('order');
    }

    /**
     * Make field routes.
     *
     * @param string $controller
     * @return void
     */
    protected function makeFieldRoutes(string $controller, string $identifier = 'id')
    {
        // For refreshing.
        RouteFacade::get("/load", [$controller, "load"])->name('load');

        // Update
        RouteFacade::put("/{{$identifier}}/{form}", [$controller, "update"])->name('update');

        // Media
        RouteFacade::put("/{{$identifier}}/{form}/media/order", [$controller, 'orderMedia'])->name("media.order");
        RouteFacade::put("/{{$identifier}}/{form}/media/{media_id}", [$controller, 'updateMedia'])->name("media.update");
        RouteFacade::post("/{{$identifier}}/{form}/media", [$controller, 'storeMedia'])->name("media.store");
        RouteFacade::delete("/{{$identifier}}{form}/media/{media_id}", [$controller, 'destroyMedia'])->name("media.destroy");

        // Blocks
        RouteFacade::get("/{{$identifier}}/{form}/blocks/{field_id}", [$controller, "loadBlocks"])->name("blocks.index");
        RouteFacade::get("/{{$identifier}}/{form}/blocks/{field_id}/{block_id}", [$controller, "loadBlock"])->name("blocks.index");
        RouteFacade::post("/{{$identifier}}/{form}/blocks/{field_id}", [$controller, "storeBlock"])->name("blocks.store");
        RouteFacade::put("/{{$identifier}}/{form}/blocks/{field_id}/{block_id}", [$controller, "updateBlock"])->name("blocks.update");
        RouteFacade::delete("/{{$identifier}}/{form}/blocks/{field_id}/{block_id}", [$controller, "destroyBlock"])->name("blocks.destroy");

        // Relations
        RouteFacade::post("/{{$identifier}}/{form}/{relation}/index", [$controller, "relationIndex"])->name("relation.index");
        RouteFacade::post("/{{$identifier}}/{form}/{relation}", [$controller, "loadRelations"])->name("relation.load");
        RouteFacade::put("/{{$identifier}}/{form}/{relation}/order", [$controller, "orderRelation"])->name("relation.order");
        RouteFacade::delete("/{{$identifier}}/{form}/{relation}/{relation_id}",  [$controller, "destroyRelation"])->name("relation.delete");
        RouteFacade::post("/{{$identifier}}/{form}/{relation}/{relation_id}", [$controller, "createRelation"])->name("relation.store");

        /*
        // Blocks Media
        Route::post("/{id}/blocks/{field_id}/{block_id}/media", [$controller, "storeBlockMedia"])->name("blocks.media.store");
        Route::put("/{id}/blocks/{field_id}/{block_id}/media/order", [$controller, 'orderBlockMedia'])->name("blocks.media.order");
        Route::put("/{id}/blocks/{field_id}/{block_id}/media/{media_id}", [$controller, "updateBlockMedia"])->name("blocks.media.update");
        Route::delete("/{id}/blocks/{field_id}/{block_id}/media/{media_id}", [$controller, "destroyBlockMedia"])->name("blocks.media.destroy");

        // Blocks Relations
        Route::post("/{id}/blocks/{field_id}/{block_id}/{relation}/index", [$controller, "blockRelationIndex"])->name("blocks.relation.index");
        Route::post("/{id}/blocks/{field_id}/{block_id}/{relation}", [$controller, "loadBlockRelations"])->name("blocks.relation.load");
        Route::put("/{id}/blocks/{field_id}/{block_id}/{relation}/order", [$controller, "orderBlockRelation"])->name("blocks.relation.order");
        Route::delete("/{id}/blocks/{field_id}/{block_id}/{relation}/{relation_id}",  [$controller, "destroyBlockRelation"])->name("blocks.relation.delete");
        Route::post("/{id}/blocks/{field_id}/{block_id}/{relation}/{relation_id}", [$controller, "createBlockRelation"])->name("blocks.relation.store");
        */
    }
}
