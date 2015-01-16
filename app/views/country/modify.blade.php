@extends('layouts.master')

@section('meta_title')
	Modification du pays {{ $country->name }}
@stop

@section('content')
	<h1>Modifier un pays</h1>

	{{ Form::model($country, array('route' => array('country_modify', $country->id))) }}
		{{ Form::label('name', 'Nom') }}
        <p>{{ Form::text('name', null, array('class' => 'form-control')) }}</p>
        <p>{{ Form::submit('Modifier', array('class' => 'btn btn-success')) }}</p>
	{{ Form::close() }}
@stop