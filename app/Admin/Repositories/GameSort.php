<?php

namespace App\Admin\Repositories;

use App\Models\GameSort as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class GameSort extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
