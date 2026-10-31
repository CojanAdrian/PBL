@if($partners->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($partners as $partner)
                    @if($partner->url)
                        <div class="col-md-4">
                            <a href="{!! $partner->url !!}">
                                <img src="{{ asset('storage/partner/'. $partner->img ) }}" alt="">
                            </a>
                        </div>
                    @else
                        <div class="col-md-4">
                            <img src="{{ asset('storage/partner/'. $partner->img ) }}" alt="">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
