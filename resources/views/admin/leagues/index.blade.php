@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader')  

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- Validation -->
<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>

<style>
    .dropdown-menu {
        min-width: auto;
    }
    #feedback { font-size: 1.4em; }
    #selectable .ui-selecting { background: none; }
    #selectable .ui-selected { background: none; color: #7ab428; }
    #selectable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #selectable tr { cursor:pointer; margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }
    table tr td{ color: rgb(207, 206, 206);}

</style>

<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
        <div class="tab-content">
            <div id="menu1" class="right-content right-content-space fixed-width">

                <div class="editor-domain-container-heading">
                    <div class="page-header">
                        <h1>Leagues  <span class="badge txt-radius2">{{@$total}}</span>
                            
                            <a href="{{url('/admin/leagues/create')}}" class="btn btn-info pull-right">Add New League</a>
                            
                        </h1>
                    </div>
                    <div class="box-content">
                        <div class="table-responsive">
                            <table class="table table-hover table-nomargin no-margin table-bordered table-striped" id="table_st">
                                <thead>
                                    <tr>
                                        <th class="width">Sr#</th>
                                        <th class="width">Code</th>
                                        <th class="width">Name</th>
                                        <th class="width">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leagues as $key => $league) 
                                    <tr>
                                        
                                        <td>{!!$key+1!!}</td>
                                        <td>{!!$league->league_code!!}</td>
                                        <td>{!!$league->name!!}</td>
                                        <td>
                                        <a class="edite-btn" href="{{ url('/admin/leagues/'.$league->id.'/edit')}}"><i class="fa fa-edit fa-fw"></i></a>
                                        
                                        {!! Form::open([
                                        'method'=>'DELETE',
                                        'url' => ['admin/leagues', $league->id],
                                        'style' => 'display:inline'
                                        ]) !!}
                                        {!! Form::button('<i class="fa fa-trash fa-fw" title="Delete League"></i>', ['class' => 'delete-form-btn delete-style']) !!}
                                        {!! Form::submit('Delete', ['class' => 'hidden deleteSubmit delete']) !!}
                                        {!! Form::close() !!}
                                        
                                    </td>
                                    </tr>
                                    @endforeach
                                    @if (count($leagues) == 0)
                                    <tr><td colspan="4"><div class="no-record-found alert alert-warning">No leagues found!</div></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <nav class="pull-right">{!! $leagues->render() !!}</nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('scripts')
@endsection
