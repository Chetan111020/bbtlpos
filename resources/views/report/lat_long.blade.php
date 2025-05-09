@extends('layouts.app')
@section('title', __('Store Late_Long'))
@section('content')

    @component('components.widget', ['class' => 'box-solid'])
        <section class="content">
            <form action="/lang" method="post">
                @csrf
                <div class="form-group">
                    {{-- <input type="text" name="id" id=""> --}}
                    <label for="">Address</label>
                    <select class="form-control" name="id" id="selectNow">
                        <option value="" selected disabled>select</option>
                        @foreach ($address as $item)
                            <option value="{{ $item->id }}">ID:{{ $item->id }}, {{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                </div>
            </form>
        </section>
    @endcomponent

@endsection
