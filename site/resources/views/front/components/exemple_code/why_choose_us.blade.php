@if($why_chooses->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($why_chooses as $why_choose)
                    <div class="col-md-4">
                        <img src="{{ asset('storage/why_choose/'. $why_choose->img ) }}" alt="">
                        <h3>
                            title - {!! $why_choose->title !!}
                        </h3>
                        <p>
                            text - {!! $why_choose->text !!}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
