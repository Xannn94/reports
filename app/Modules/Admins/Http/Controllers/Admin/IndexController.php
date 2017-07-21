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
            'premium'   => 'required|max:10',
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

        $params = $request->all();

        if ($entity->name !== $params['name']){
            $id = $this->getRedmineId($params['name']);

            if ($id){
                $params['redmine_id'] = $id;
            }
            else{
                return redirect()->back()->withInput()->with('message','Такого пользователя не существует в redmine');
            }
        }

        $entity->update($params);

        $this->after($entity);

        return redirect()->back()->with('message', trans($this->messages['update']));

    }

    public function store(Request $request){

        $this->validate($request, $this->getRules($request));

        $params = $request->all();

        $id = $this->getRedmineId($params['name']);

        if ($id){
            $params['redmine_id'] = $id;
        }
        else{
            return redirect()->back()->withInput()->with('message','Такого пользователя не существует в redmine');
        }


        $entity = $this->getModel()->create($params);

        $this->after($entity);

        return redirect()->route($this->routePrefix.'edit', ['id'=>$entity->id])->with('message', trans($this->messages['store']));
    }

    private function getRedmineId($name){
        $json = json_encode([
            'Login' => $name,
        ]);
        $ch = curl_init('http://report.web2.weltkind.ru/get-id');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;charset=\"utf-8\"',
            'Content-Length: ' . strlen($json)
        ]);

        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($result);

        if($result->status == 'success'){
            return $result->id;
        }
        else {
            return 0;
        }
    }
}
