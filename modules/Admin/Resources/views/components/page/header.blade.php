@section('title')
    @isset($subtitle)
        {{  "{$subtitle} - {$title}" }}
    @else
        {{ $title }}
    @endisset
@endsection

@section('content_header')
    <h3>{{ $title }}</h3>
@endsection

@section('breadcrumb')
    {{ $slot }}
@endsection
