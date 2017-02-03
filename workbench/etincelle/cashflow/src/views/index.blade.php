@extends('layouts.master')

@section('meta_title')
    Trésorerie
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Trésorerie</h2>
        </div>
        <div class="col-sm-2">
            <div class="title-action">

            </div>
        </div>

    </div>
@stop

@section('content')
    @foreach($accounts as $account)
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        {{$account->name}}
                        <a href="{{ URL::route('cashflow_operation_add', $account->id) }}"
                           class="btn btn-xs btn-primary pull-right">Nouvelle
                            opération</a>
                    </div>
                    <div class="ibox-content">
                        {{ Form::model($account, array('route' => array('cashflow_account_modify_check', $account->id))) }}
                        {{ Form::label('amount', 'Solde actuel') }}
                        <div class="input-group">
                            {{ Form::text('amount', isset($account)?$account->amount:'', array('class' => 'form-control')) }}

                            <span class="input-group-btn">
                            {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                                </span>
                        </div>
                        {{ Form::close() }}


                        <table class="table table-condensed table-striped">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Operations</th>
                                <th>Solde</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($account->getDailyOperations() as $date => $data)
                                <tr>
                                    <td>{{date('d/m/Y', strtotime($date))}}</td>
                                    <td>
                                        <table class="table table-condensed">
                                            @foreach($data['operations'] as $operation)
                                                <tr>
                                                    <td class="col-lg-1">
                                                        @if ($operation['id'])
                                                            <a href="{{ URL::route('cashflow_operation_delete', array('account_id' => $account->id,'id' => $operation['id'])) }}"
                                                               class="btn btn-xs btn-danger m-xxs"><i
                                                                        class="fa fa-close"></i></a>
                                                        @endif
                                                    </td>
                                                    <td class="col-lg-7">
                                                        @if ($operation['id'])
                                                            <a href="{{ URL::route('cashflow_operation_modify', array('account_id' => $account->id,'id' => $operation['id'])) }}">{{$operation['name']}}</a>
                                                        @else
                                                            {{$operation['name']}}
                                                        @endif
                                                        @if ($operation['comment'])
                                                            <p class="text-muted">{{$operation['comment']}}</p>
                                                        @endif
                                                    </td>
                                                    <td class="text-right col-lg-2">
                                                        @if ($operation['amount'] < 0)
                                                            <span style="color: red">{{ number_format( $operation['amount'], 2, ',', '.') }}
                                                                €</span>
                                                        @else
                                                            <span style="color: green">{{ number_format( $operation['amount'], 2, ',', '.') }}
                                                                €</span>
                                                        @endif
                                                    </td>
                                                    <td class="col-lg-2">
                                                        @if ($operation['id'])
                                                            <div class="pull-right">
                                                                @if($operation['refreshable'])
                                                                    <a href="{{ URL::route('cashflow_operation_refresh', array('account_id' => $account->id,'id' => $operation['id'])) }}"
                                                                       class="btn btn-xs btn-default m-xxs"><i
                                                                                class="fa fa-refresh"></i></a>
                                                                @endif
                                                                <a href="{{ URL::route('cashflow_operation_archive', array('account_id' => $account->id,'id' => $operation['id'])) }}"
                                                                   class="btn btn-xs btn-primary m-xxs"><i
                                                                            class="fa fa-check text-primary"></i></a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                    <td class="text-right">
                                        @if ($data['amount'] < 0)
                                            <span style="color: red">{{ number_format( $data['amount'], 0, ',', '.') }}
                                                €</span>
                                        @else
                                            <span style="color: green">{{ number_format( $data['amount'], 0, ',', '.') }}
                                                €</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    @endforeach
@stop





@section('stylesheets')

    <style type="text/css">

    </style>

@stop

@section('javascript')

    <script type="text/javascript">

    </script>

@stop



