@if($paginator->hasPages())

    <ul class="pagination">

        <li class="page-item">
            @if(!$paginator->onFirstPage())
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                    {{ __('Previous') }}
                </a>
            @endif
        </li>
        <li class="page-item">
            @if($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                    {{ __('Next') }}
                </a>
            @endif
        </li>
    </ul>
@endif

