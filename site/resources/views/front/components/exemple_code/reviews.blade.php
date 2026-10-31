@if($reviews->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($reviews as $review)
                    <div class="col-md-4">
                        <h4>
                            Title - {!! $review->title !!}
                        </h4>
                        <p>
                            text - {!! $review->text !!}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
