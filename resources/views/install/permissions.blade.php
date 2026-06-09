<template x-if="step === 3">
    <div class="has-scrollable-content permissions d-flex flex-column">
        <div class="header overflow-hidden">
            <h3>{{ trans('install.permissions.title') }}</h3>
            <p class="excerpt">{{ trans('install.permissions.excerpt') }}</p>
        </div>

        <div class="content position-relative flex-grow-1 overflow-hidden">
            <div class="scrollable-content">
                <div class="box">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('install.permissions.files') }}</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permission->files() as $label => $satisfied)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>@include('install.partials.status-icon', ['satisfied' => $satisfied])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('install.permissions.directories') }}</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permission->directories() as $label => $satisfied)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>@include('install.partials.status-icon', ['satisfied' => $satisfied])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
