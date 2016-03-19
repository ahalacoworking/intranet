@extends('layouts.master')

@section('content')

    @if (Auth::user()->isSuperAdmin())
        <div class="row">
            <div class="col-lg-2">
                @include('partials.sales.monthly.component')
            </div>
            <div class="col-lg-2">
                @include('partials.sales.yearly.component')
            </div>
            <div class="col-lg-2">
                @include('partials.pending.component')
            </div>
            <div class="col-lg-2">
                @include('partials.charges.component')
            </div>
            <div class="col-lg-4">
                @include('partials.checkin.status')
            </div>
        </div>
    @endif
    <div class="hr-line-dashed"></div>

    <div class="row">
        <div class="col-lg-9">
            @include('partials.wall.component')
        </div>
        <div class="col-lg-3">
            @if (Auth::user()->isSuperAdmin())
            @elseif (Auth::user()->role == 'member')
                @include('partials.checkin.component')
                @include('partials.active_subscription')
                @include('partials.member.component')
            @endif
            @include('booking::partials.ressource_booking_status')
            @include('booking::partials.upcoming_events')
            @include('partials.next_birthday.component')
        </div>
    </div>

@stop

@section('javascript')
    {{ HTML::script('js/jquery.waypoints.min.js') }}
    {{ HTML::script('js/infinite.min.js') }}
@stop




