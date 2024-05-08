<?php

namespace App\Admin\Repositories;

use App\Models\GameSortTree as Model;
use Dcat\Admin\Contracts\TreeRepository;
use Dcat\Admin\Repositories\EloquentRepository;

class GameSortTree extends EloquentRepository implements TreeRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

}
