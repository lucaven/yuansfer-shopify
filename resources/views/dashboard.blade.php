@extends('shopify-app::layouts.default')

@section('styles')
    <link rel="stylesheet" href="/css/app.css" />
@endsection
@section('content')



    <div class="container align-content-center">

        <div class="row">
            <div class="col-md-8">
                <h1 class="title">Yuansfer</h1>
                <hr/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mgb-10">About Yuansfer</h3>
                        Learn more about <a href="https://www.yuansfer.com/" target="_blank">Yuansfer</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <form method="POST">
                <div class="card">
                    <div class="card-body">
                            @csrf
                            <div class="form-group">
                                <label>Merchant number</label>
                                <input value="{{$config['merchantNo']}}" type="text" class="form-control" name="merchantNo" required>
                            </div>

                            <div class="form-group">
                                <label>Store Number</label>
                                <input value="{{$config['storeNo']}}"  type="text" class="form-control" name="storeNo" required>
                            </div>

                            <div class="form-group">
                                <label for="created_at_max">API Token</label>
                                <input value="{{$config['token']}}" type="password" class="form-control" id="token" name="token" required>
                                <a href="#" onclick="showApi()">Show</a>
                            </div>

                            <input type="hidden" name="test" id="useTest" value="@if($config['test']) on @endif">
                    </div>
                </div>
                    <div class="button-wrapper">
                        <button type="submit" class="btn btn-primary mgt-10">Activate Yuansfer</button>
                    </div>
                </form>

            </div>
        </div>

    </div>




@endsection

@section('scripts')
    @parent

    <script type="text/javascript">
        var AppBridge = window['app-bridge'];
        var actions = AppBridge.actions;
        var TitleBar = actions.TitleBar;
        var Button = actions.Button;
        var Redirect = actions.Redirect;
        var titleBarOptions = {
            title: 'Welcome',
        };
        var myTitleBar = TitleBar.create(app, titleBarOptions);

        @if (\Session::has('success'))
        actions.Toast.create(app, {
            message: 'Settings saved',
            duration: 5000,
        }).dispatch(actions.Toast.Action.SHOW);
        @endif

        function showApi() {
            var passwordField = document.getElementById('token');
            var value = passwordField.value;

            if(passwordField.type === 'password') {
                passwordField.type = 'text';
            }
            else {
                passwordField.type = 'password';
            }

            passwordField.value = value;
        }
    </script>
@endsection
