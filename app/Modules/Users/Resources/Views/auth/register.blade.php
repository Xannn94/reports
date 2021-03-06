@push('js')
    <script>
        $(document).ready(function () {
            $('.auth').show(1000);
        });
    </script>
@endpush

@section('meta-title')
    Регистрация
@endsection

@extends('layouts.inner')

@section('h1','Регистрация')

@section('content')
    @if(Session::get('message'))
        <div class="auth">
            <div class="success">
                <p>
                    {{ Session::get('message') }}
                </p>
            </div>

        </div>
    @else

        <form class="form-horizontal" role="form" method="POST" action="{{ route('user.register.post') }}">
            {{ csrf_field() }}

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="inputEmail" class="col-lg-1 control-label">@lang('users::index.register.input.name')</label>
                <div class="col-lg-4">
                    <input type="text" class="form-control" id="name" name="name" placeholder="@lang('users::index.register.input.name')" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <span class="error">
                            {{ $errors->first('name') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="inputEmail" class="col-lg-1 control-label">@lang('users::index.register.input.email')</label>
                <div class="col-lg-4">
                    <input type="text" class="form-control" id="email" name="email" placeholder="@lang('users::index.register.placeholder.email')" value="{{ old('email') }}">
                    @if ($errors->has('email'))
                        <span class="error">
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="inputEmail" class="col-lg-1 control-label">@lang('users::index.register.input.password')</label>
                <div class="col-lg-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="@lang('users::index.register.input.password')" value="{{ old('password') }}">
                    @if ($errors->has('password'))
                        <span class="error">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                <label for="password_confirmation" class="col-lg-1 control-label">@lang('users::index.register.input.password_confirmation')</label>
                <div class="col-lg-4">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="@lang('users::index.register.input.password_confirmation')" value="{{ old('password_confirmation') }}">
                    @if ($errors->has('password'))
                        <span class="error">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('captcha') ? 'has-error' : '' }}">
                <label class="captcha-label col-lg-1 control-label" for="captcha">
                    <span class="captcha">
                        <a href="" title="Reload image">{!! captcha_img('mini') !!}</a>
                    </span>
                </label>
                <div class="col-lg-4">
                    <input type="text" class="form-control" id="captcha" name="captcha" placeholder="@lang('users::index.register.input.captcha')" value="{{ old('captcha') }}">
                    @if ($errors->has('captcha'))
                        <span class="error">
                            {{ $errors->first('captcha') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
{{--    <div class="registration">
        <form  role="form" method="POST" action="{{ url('/register') }}">
            {{ csrf_field() }}
            <div class="form-row">
                <label class="info-label" for="name_feed_top">Имя<span class="need">*</span></label>
                <div class="input-row">
                    <input name="name" class="cabinet-input {{ $errors->has('name') ? 'error' : '' }}" type="text" id="name_feed_top" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <span class="error">
                            {{ $errors->first('name') }}
                        </span>
                    @endif
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-row">
                <label class="info-label" for="surname">Фамилия<span class="need">*</span></label>
                <div class="input-row">
                    <input name="surname" class="cabinet-input {{ $errors->has('surname') ? 'error' : '' }}" type="text" id="surname" value="{{ old('surname') }}">
                    @if ($errors->has('surname'))
                        <span class="error">
                            {{ $errors->first('surname') }}
                        </span>
                    @endif
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-row">
                <label class="info-label" for="mail">E-mail<span class="need">*</span></label>
                <div class="input-row">
                    <input name="email" class="cabinet-input {{ $errors->has('email') ? 'error' : '' }}" type="text" id="mail" value="{{ old('email') }}">
                    @if ($errors->has('email'))
                        <span class="error">
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-row">
                <label class="info-label" for="mail_feed_top">Пароль<span class="need">*</span></label>
                <div class="input-row">
                    <input name="password" class="cabinet-input {{ $errors->has('password') ? 'error' : '' }}" type="password" id="mail_feed_top">
                    @if ($errors->has('password'))
                        <span class="error">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-row">
                <label class="info-label" for="mail_feed">Повторите пароль<span class="need">*</span></label>
                <div class="input-row">
                    <input  name="password_confirmation" class="cabinet-input" type="password" id="mail_feed">
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-row form-row_captcha">
                <label class="info-label" for="captcha">
                    <span class="captcha">
                        <a href="" title="Reload image">{!! captcha_img('flat') !!}</a>
                    </span>
                    <span class="need">*</span>
                </label>
                <div class="input-row">
                    <input class="cabinet-input {{ $errors->has('captcha') ? 'error' : '' }}" type="text" id="captcha" name="captcha">
                    @if ($errors->has('captcha'))
                        <span class="error">
                                        {{ $errors->first('captcha') }}
                        </span>
                    @endif
                </div>

                <div class="clear"></div>
            </div>
            <button class="main-order-btn main-order-btn_reg">Зарегистрироваться</button>
        </form>
    </div>--}}

    @endif
@endsection