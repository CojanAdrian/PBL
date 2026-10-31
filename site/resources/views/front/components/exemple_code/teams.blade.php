@if($teams->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($teams as $team)

                        <div class="col-md-4">
                            <img src="{{ asset('storage/team/'. $team->img ) }}" alt="">
                            <h3>
                              Name -  {!! $team->name !!}
                            </h3>
                            <h6>
                              Function - {!! $team->function !!}
                            </h6>
                            <p>
                              Description - {!! $team->text !!}
                            </p>
                        </div>

                @endforeach
            </div>
        </div>
    </section>
@endif
