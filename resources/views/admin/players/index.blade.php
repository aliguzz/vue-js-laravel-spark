@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader')

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- Validation -->

<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>

<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>

<style>
    button,
    input,
    optgroup,
    select,
    textarea {
        margin-bottom: 12px !important;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }

    .edite-btn {
        background: #7ab428;
        color: #fff;
        border: 1px solid #7ab428;
        border-radius: 100px !important;
        margin-left: 0px !important;
        padding: 5px 4px 3px 5px !important;
    }

    .delete-style {
        background: #dc3545;
        color: #fff;
        border: 1px solid #dc3545;
        padding: 3px 4px !important;
        border-radius: 100px;
        margin-left: 0px !important;
    }

    .editor-domain-container-heading {
        max-width: 1098px;
        margin: 0px auto;
        margin-left: 49px !important;
    }

    .edite-btn {
        background: #7ab428;
        color: #fff;
        border: 1px solid #7ab428;
        padding: 10px;
        border-radius: 100px;
        margin-left: 0px !important;
    }

    .dropdown-menu {
        min-width: auto;
    }

    #feedback {
        font-size: 1.4em;
    }

    #selectable .ui-selecting {
        background: none;
    }

    #selectable .ui-selected {
        background: none;
        color: #7ab428;
    }

    #selectable {
        list-style-type: none;
        margin: 0;
        padding: 0;
        width: 60%;
    }

    #selectable tr {
        cursor: pointer;
        margin: 3px;
        padding: 0.4em;
        font-size: 1.4em;
        height: 18px;
    }

    table tr td {
        color: #565151
    }

    table tr th {
        color: black
    }

</style>

<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
        <div class="tab-content">
            <div id="menu1" class="right-content right-content-space fixed-width">

                <div class="editor-domain-container-heading">
                    <div class="page-header">
                        <h1>Players <span class="badge txt-radius2">{{@$total}}</span>

                            <a href="{{url('/admin/players/create')}}" class="btn btn-info pull-right">Add New
                                Player</a>
                            <div class="clearfix"></div>
                        </h1>
                    </div>
                    <div class="box-content">
                        <div class="table-responsive">
                            <table id="players_table" class="table table-hover no-margin table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Player Name</th>
                                        <th>Colours</th>
                                        <th>Injured Available</th>
                                        <th>Injured Out</th>
                                        <th>Missing</th>
                                        <th>Suspended</th>
                                        <th>Cost</th>
                                        <th>Position</th>
                                        <th>Club</th>
                                        <th>Points</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($players as $item)
                                    <tr>
                                        <td>{!! $item->name !!}</td>

                                        <td><img class="image-display" id="image_upload_preview" height="25px"
                                                src="{{URL::to('uploads/clubs/'.$item->shirt)}}" /></td>
                                        <td>@if($item->injured_available == 1) <label
                                                class="label label-success">Yes</label> @else <label
                                                class="label label-danger">No</label> @endif</td>

                                        <td>@if($item->injured_out == 1) <label class="label label-success">Yes</label>
                                            @else <label class="label label-danger">No</label> @endif</td>
                                        
                                        <td>@if($item->missing == 1) <label class="label label-success">Yes</label>
                                            @else <label class="label label-danger">No</label> @endif</td>
                                        
                                        <td>@if($item->suspended == 1) <label class="label label-success">Yes</label>
                                            @else <label class="label label-danger">No</label> @endif</td>

                                        <td>£{{ $item->cost }}M</td>
                                        <td>{{ $item->position }}</td>
                                        <td>{{ $item->club_name }}</td>
                                        <td>{{ $item->points }}</td>
                                        <td>


                                            <a class="edite-btn" href="{{ url('/admin/players/'.$item->id.'/edit')}}"><i
                                                    class="fa fa-edit fa-fw"></i></a>


                                            {!! Form::open([
                                            'method'=>'DELETE',
                                            'url' => ['admin/players', $item->id],
                                            'style' => 'display:inline'
                                            ]) !!}
                                            {!! Form::button('<i class="fa fa-trash fa-fw" title="Delete Player"></i>',
                                            ['class' => 'delete-form-btn delete-style']) !!}
                                            {!! Form::submit('Delete', ['class' => 'hidden deleteSubmit delete']) !!}
                                            {!! Form::close() !!}


                                        </td>
                                    </tr>
                                    @endforeach
                                    @if (count($players) == 0)
                                    <tr>
                                        <td colspan="9">
                                            <div class="no-record-found alert alert-warning">No players found!</div>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        {{-- <nav class="pull-right">{!! $players->render() !!}</nav> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $('#players_table').DataTable();
        $('.remove_btn').click(function () {

            $("#image-upload").val('');

        });

    });

</script>

@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $.uploadPreview({
            input_field: "#image-upload", // Default: .image-upload
            preview_box: "#image-preview", // Default: .image-preview
            label_field: "#image-label", // Default: .image-label
            label_default: "Choose Logo", // Default: Choose File
            label_selected: "Change Logo", // Default: Change File
            no_label: false // Default: false
        });
    });

</script>
@endsection
