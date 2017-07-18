<?php

namespace App\Modules\Admins\Http\Controllers\Admin;
use App\Modules\Admin\Models\Admin as Model;
use View;
use App\Modules\Admin\Http\Controllers\Admin;
use App\Modules\Roles\Models\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class IndexController extends Admin
{
    /* тут должен быть slug модуля для правильной работы меню */
    public $page = 'admins';
    /* тут должен быть slug группы для правильной работы меню */
    public $pageGroup = 'users';

    public function getModel(){
        return new Model();
    }

    public function getRules($request, $id = false){
        $rules = [
            'premium'     => 'required|max:10',
            'name'      => 'required|max:255',
            'email'     => 'required|email|max:255|unique:admins'.($id?',id,'.$id:''),
            'password'  => 'required|min:6'
        ];


        if (isset($request->password) && !$request->password){
            unset($request->password);
            unset($rules['password']);
        }

        return $rules;
    }

    public function create(){
        $entity = $this->getModel();

        $this->after($entity);

        return view($this->getFormViewName(), [
            'entity'    => $entity,
            'roles'     => Roles::getSelect()
        ]);
    }

    public function edit($id)
    {
        $entity = $this->getModel()->findOrFail($id);

        View::share('entity', $entity);

        $this->after($entity);

        return view(
            $this->getFormViewName(),
            [
                'entity'=>$entity,
                'routePrefix'=>$this->routePrefix,
                'roles'     => Roles::getSelect()
            ]
        );
    }

    public function destroy($id){
        if (Auth::guard('admin')->user()->id == $id){
            abort(403);
        }

        return parent::destroy($id);
    }

    public function update(Request $request, $id){
        $this->validate($request, $this->getRules($request, $id));

        $entity = $this->getModel()->findOrFail($id);

        $entity->update($request->all());

        $this->after($entity);

        return redirect()->back()->with('message', trans($this->messages['update']));

    }
}
