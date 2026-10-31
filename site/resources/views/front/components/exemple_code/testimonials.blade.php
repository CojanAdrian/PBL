@if($testimonials->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($testimonials as $testimonial)
                    <div class="col-md-4">
                        <img src="{{ asset('storage/testimonial/'. $testimonial->img ) }}" alt="">
                        <h4>
                            Name - {!! $testimonial->name !!}
                        </h4>
                        <h6>
                            Function - {!! $testimonial->function !!}
                        </h6>
                        <p>
                            text - {!! $testimonial->text !!}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
