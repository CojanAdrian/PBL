@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE




    {{--    CONTACT FORM--}}

    <form id="contact"
          action="{{ route('front.contact.storage', app()->getLocale()) }}"
          method="post">
        @csrf
        <div class="form-group">
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   autocomplete="off">
            <span>
             name
            </span>
        </div>
        <!-- end form-group -->
        <div class="form-group">
            <input type="text"
                   name="email"
                   value="{{ old('email') }}"
                   autocomplete="off">

            <span>
                                  email
                                </span>
        </div>
        <!-- end form-group -->
        <div class="form-group">
            <input type="text"
                   name="subject"
                   value="{{ old('subject') }}"
                   autocomplete="off">

            <span>
                                 subject
                                </span>
        </div>
        <div class="form-group">
            <input type="text"
                   name="phone"
                   value="{{ old('phone') }}"
                   autocomplete="off">

            <span>
                                 phone
                                </span>
        </div>
        <!-- end form-group -->
        <div class="form-group">
            <div class="form-group">
                                <textarea name="message"
                                          autocomplete="off">{{ old('message') }}</textarea>

                <span>
                                 message
                                </span>
            </div>
        </div>
        <!-- end form-group -->
        <div class="form-group">
            <button type="submit"
                    name="submit">
                <strong>
                   submit
                </strong>
            </button>
        </div>
    </form>

    @if($errors->any())
        <div class="container">
            <div class="row">
                <div class="col-md-10">
                    <div class="aler alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{--  END  CONTACT FORM--}}




@endsection
