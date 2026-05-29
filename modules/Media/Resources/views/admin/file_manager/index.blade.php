<!DOCTYPE html>
<html lang="{{ locale() }}">
    <head>
        <meta charset="UTF-8">

        <title>{{ trans('media::media.file_manager.title') }}</title>

        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:600|Roboto" rel="stylesheet">
        <script src="{{ v(asset('build/assets/jquery.min.js')) }}"></script>
        <script src="{{ v(asset('build/assets/bootstrap.min.js')) }}"></script>

        @vite([
            'modules/Admin/Resources/assets/sass/main.scss',
            'modules/Admin/Resources/assets/js/main.js',
            'modules/Admin/Resources/assets/js/app.js',
            'modules/Media/Resources/assets/admin/sass/main.scss',
            'modules/Media/Resources/assets/admin/js/main.js'
        ])

        @include('admin::partials.globals')
    </head>

    <body class="file-manager {{ is_rtl() ? 'rtl' : 'ltr' }}">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-body">
                            @include('media::admin.media.partials.uploader')
                            @include('media::admin.media.partials.grid', ['pickerMode' => true, 'type' => $type])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="notification-toast"></div>

        @include('admin::partials.confirmation_modal')
    </body>
</html>
