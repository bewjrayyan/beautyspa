<template x-if="step === 2">
    <div class="has-scrollable-content requirements d-flex flex-column">
        <div class="header overflow-hidden">
            <h3>{{ trans('install.requirements.title') }}</h3>
            <p class="excerpt">{{ trans('install.requirements.excerpt') }}</p>
        </div>

        <div class="content position-relative flex-grow-1 overflow-hidden">
            <div class="scrollable-content">
                <div class="box">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('install.requirements.php') }}</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requirement->php() as $label => $satisfied)
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
                                    <th>{{ trans('install.requirements.extensions') }}</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requirement->extensions() as $label => $satisfied)
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
                                    <th>{{ trans('install.requirements.packages') }}</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requirement->packages() as $label => $satisfied)
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
