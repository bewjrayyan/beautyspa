<div class="account-download-cards d-lg-none">
    @foreach ($downloads as $download)
        <div class="account-download-card">
            <div class="account-download-card__info">
                <i class="las la-file-alt" aria-hidden="true"></i>
                <span class="account-download-card__name">{{ $download->filename }}</span>
            </div>

            <a href="{{ route('account.downloads.show', encrypt($download->id)) }}" title="{{ trans('storefront::account.downloads.download') }}" class="account-download-card__btn">
                <i class="las la-cloud-download-alt"></i>
                {{ trans('storefront::account.downloads.download') }}
            </a>
        </div>
    @endforeach
</div>

<div class="table-responsive d-none d-lg-block">
    <table class="table table-borderless my-downloads-table">
        <thead>
            <tr>
                <th>{{ trans('storefront::account.downloads.filename') }}</th>
                <th>{{ trans('storefront::account.action') }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($downloads as $download)
                <tr>
                    <td>
                        {{ $download->filename }}
                    </td>

                    <td>
                        <a href="{{ route('account.downloads.show', encrypt($download->id)) }}" title="{{ trans('storefront::account.downloads.download') }}" class="btn btn-download">
                            <i class="las la-cloud-download-alt"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
