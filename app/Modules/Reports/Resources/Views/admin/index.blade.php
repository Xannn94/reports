@extends('admin::layouts.app')

@section('topmenu')
    <a class="btn btn-success" href="{{ route('admin.reports.show','refresh')}}">
        <i class="glyphicon glyphicon-list-alt"></i>
        Обновить мои тикеты
    </a>
    <a class="btn btn-primary" href="{{route('admin.reports.index',['filter' => 'last'])}}">
        <i class="glyphicon glyphicon-list-alt"></i>
        Отчёт за прошлый месяц
    </a>
    <a class="btn btn-primary" href="{{ route('admin.reports.index',['filter' => 'current'])}}">
        <i class="glyphicon glyphicon-list-alt"></i>
        Отчёт за текущий месяц
    </a>

    @if(isset($filter))
        <a
            class="btn btn-success"
            href="{{ route('admin.reports.show',['report' => 'download', 'filter' => $filter])}}">
            <i class="glyphicon glyphicon-list-alt"></i>
            Скачать Отчёт
        </a>
    @endif
@endsection

@section('content')
    @include('admin::common.errors')

    @if(!$entities->isEmpty())
    <table class="table table-bordered table-hover ">
        <thead>
            <tr>
                <th>id</th>
                <th>Дата обновления</th>
                <th>Проект</th>
                <th>Название тикета</th>
                <th>Статус</th>
                <th>Оптимизм</th>
                <th>Пессимизм</th>
                <th>Затрачено</th>
                <th width="100">Удалить?</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entities as $entity)
                <tr>
                    <td>{{ $entity->id }}</td>
                    <td>{{ $entity->updated }}</td>
                    <td>{{ $entity->project }}</td>
                    <td>{{ $entity->ticket }}</td>
                    <td>{{ $entity->status }}</td>
                    <td>{{ $entity->optimism }}</td>
                    <td>{{ $entity->pessimism }}</td>
                    <td>{{ $entity->total }}</td>
                    <td>
                        @if(Auth::guard('admin')->user()->canUpdate())
                            <a class="btn btn-primary btn-sm" title="@lang('admin::admin.edit')" href="{!! route($routePrefix.'edit', ['id' => $entity->id]) !!}">
                                <i class="glyphicon glyphicon-pencil"></i>
                            </a>
                        @endif

                        @if(Auth::guard('admin')->user()->canDelete())
                            {!! Form::open(['route' => [$routePrefix.'destroy', 'id'=>$entity->id], 'method' => 'delete']) !!}
                            <button type="submit" class="btn btn-danger btn-sm" title="@lang('admin::admin.delete')">
                                <i class="glyphicon glyphicon-trash"></i>
                            </button>
                            {!! Form::close() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <h2 class="text-primary text-bold">Выберите отчёт</h2>
    @endif
@overwrite
