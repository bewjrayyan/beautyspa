<template x-if="step === 1">
    <div class="has-scrollable-content welcome d-flex flex-column">
        <div class="header overflow-hidden">
            <h3>{{ trans('install.welcome.title') }}</h3>
            <p class="excerpt">{{ trans('install.welcome.lead') }}</p>
        </div>

        <div class="content position-relative flex-grow-1 overflow-hidden">
            <div class="scrollable-content">
                <div class="box">
                    <div class="title">
                        <h5>{{ trans('install.welcome.upload_title') }}</h5>
                    </div>

                    <ol class="install-checklist">
                        @foreach (trans('install.welcome.upload_steps') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </div>

                <div class="box">
                    <div class="title">
                        <h5>{{ trans('install.welcome.packages_title') }}</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @foreach ($uploadChecks as $key => $passed)
                                    <tr>
                                        <td>{{ trans('install.packages.'.$key) }}</td>
                                        <td>
                                            @if ($passed)
                                                <svg class="mdi-checkbox-marked-circle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" /></svg>
                                            @else
                                                <svg class="mdi-close-circle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2C6.47,2 2,6.47 2,12C2,17.53 6.47,22 12,22C17.53,22 22,17.53 22,12C22,6.47 17.53,2 12,2M14.59,8L12,10.59L9.41,8L8,9.41L10.59,12L8,14.59L9.41,16L12,13.41L14.59,16L16,14.59L13.41,12L16,9.41L14.59,8Z" /></svg>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box">
                    <p class="mb-1"><strong>{{ trans('install.welcome.detected_url') }}:</strong> {{ $suggestedAppUrl }}</p>
                    <p class="mb-0 text-muted">
                        {{ trans('install.welcome.document_root') }}:
                        @if (($hosting['document_root'] ?? '') === 'public')
                            {{ trans('install.welcome.document_root_public') }}
                        @elseif (($hosting['document_root'] ?? '') === 'root')
                            {{ trans('install.welcome.document_root_root') }}
                        @else
                            {{ trans('install.welcome.document_root_unknown') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
