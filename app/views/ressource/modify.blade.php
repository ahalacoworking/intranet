@extends('layouts.master')

@section('meta_title')
    Modification de la ressource {{$ressource->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-ls-12">
            <h2>Modification de la ressource {{$ressource->name}}</h2>

        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    {{ Form::model($ressource, array('route' => array('ressource_modify', $ressource->id))) }}
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('name', 'Nom de la ressource') }}
                            <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('amount', 'Valeur') }}
                            <p>{{ Form::number('amount', null, array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('order_index', 'Ordre d\'affichage') }}
                            <p>{{ Form::number('order_index', null, array('class' => 'form-control', 'min' => 1)) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p>
                                {{ Form::checkbox('is_bookable', true) }}
                                {{ Form::label('is_bookable', 'Réservable') }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('booking_background_color', 'Couleur de fond') }}
                            <p>
                                {{ Form::text('booking_background_color', null, array('class' => 'form-control')) }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            {{ Form::label('booking_text_color', 'Couleur du texte') }}
                            <p>
                                {{ Form::text('booking_text_color', null, array('class' => 'form-control')) }}
                            </p>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('ressource_list') }}" class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>
@stop