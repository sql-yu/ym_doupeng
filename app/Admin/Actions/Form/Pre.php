<?php

namespace App\Admin\Actions\Form;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Form\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Game as GameModel;

class Pre extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '上一个 ';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    // public function handle(Request $request)
    // {
    //     // dump($this->getKey());

    //     return $this->response()
    //         ->success('Processed successfully.')
    //         ->redirect('/');
    // }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
        $key = $this->getKey();
        $nextModel = GameModel::where('id','<',$key)->first();
        if($nextModel){
            return admin_url("game/{$nextModel->id}/edit");
        }else{
            return admin_url("game");
        }
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		// return ['Confirm?', 'contents'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
