@extends('admin::admin.form')

@section('topmenu')
    <div class="header-module-controls">
        <a class="btn btn-primary" href="{{route('admin.reports.index',['filter' => 'last'])}}">
            <i class="glyphicon glyphicon-list-alt"></i>
            Отчёт за прошлый месяц
        </a>
        <a class="btn btn-primary" href="{{ route('admin.reports.index',['filter' => 'current'])}}">
            <i class="glyphicon glyphicon-list-alt"></i>
            Отчёт за текущий месяц
        </a>
    </div>
@endsection

@section('form_content')

    {!! BootForm::open(['model' => $entity, 'store' => $routePrefix.'store', 'update' => $routePrefix.'update', 'autocomplete' => 'off',
   'files' => true]) !!}

    {{--Пример текстового поля--}}

    <div class="col-md-6">
        {!! BootForm::text('project', 'Проект',$entity->project,['disabled']) !!}
    </div>

    <div class="col-md-6">
        {!! BootForm::text('ticket', 'Тикет',$entity->ticket,['disabled']) !!}
    </div>

    <div class="col-md-6">
        {!!
            BootForm::select('status', 'Статус', [
                'Новая'                     => 'Новая',
                'В работе'                  => 'В работе',
                'Решена'                    => 'Решена',
                'Обратная связь'            => 'Обратная связь',
                'Закрыта'                   => 'Закрыта',
                'Отказ'                     => 'Отказ',
                'Заново открыта'            => 'Заново открыта',
                'Исследование'              => 'Исследование',
                'Ожидает тестирования'      => 'Ожидает тестирования',
                'В процессе тестирования'   => 'В процессе тестирования'
            ])
        !!}
    </div>

    <div class="col-md-6">
        {!! BootForm::text('optimism', 'Оптимизм',$entity->optimism) !!}
    </div>

    <div class="col-md-6">
        {!! BootForm::text('pessimism', 'Пессимизм',$entity->pessimism) !!}
    </div>

    <div class="col-md-6">
        {!! BootForm::text('total', 'Затрачено',$entity->total) !!}
    </div>

    <div class="col-md-6">
        {!! BootForm::textarea('comment', 'Комментарий',$entity->comment) !!}
    </div>


    {{--Чтобы были seo поля раскоментируйте--}}

    {{--@include('admin::common.forms.seo')--}}


@endsection