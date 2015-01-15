@extends('layouts.master')

@section('meta_title')
	Liste des organismes
@stop

@section('content')
	<h1>Liste des organismes</h1>
	
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Nom</th>
				<th>Dernière modification</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($organisations as $orga)
			<tr>
				<td>{{ $orga->id }}</td>
				<td>
					<a href="{{ URL::route('organisation_modify', $orga->id) }}">{{ $orga->name }}</a>
				</td>
				<td>{{ $orga->updated_at }}</td>
			</tr>
		@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">{{ $organisations->links() }}</td>
			</tr>
		</tfoot>
	</table>
@stop