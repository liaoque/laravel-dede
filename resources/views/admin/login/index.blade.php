@extends("admin.layout.login")

@section('content')

<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>管理系统</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">登陆</p>

        <form action="{{route('admin.login')}}" method="post">
            {{csrf_field()}}
            <div class="form-group has-feedback">
                <input name="userName" class="form-control" placeholder="用户名">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input name="password" type="password" class="form-control" placeholder="密码">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            @include("admin.layout.error")
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat btn-ajax">登陆</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@endsection