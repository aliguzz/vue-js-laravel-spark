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

    .delete-style {
        background: #dc3545;
        color: #fff;
        border: 1px solid #dc3545;
        padding: 3px 4px !important;
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



.main {
    width: 50%;
    margin: 50px auto;
}

/* Bootstrap 4 text input with search icon */

.has-search .form-control {
    padding-left: 2.375rem;
}

.has-search .form-control-feedback {
    position: absolute;
    z-index: 2;
    display: block;
    width: 2.375rem;
    height: 2.375rem;
    line-height: 2.375rem;
    text-align: center;
    pointer-events: none;
    color: #aaa;
}

</style>
<h1>Users <span class="badge">{{@$total}}</span>
    @if(have_premission(array(33)))
    <a href="{{url('/admin/users/create')}}" class="btn btn-info pull-right">Add New User</a>
    @endif
    <div class="clearfix"></div>
</h1>
<div class="col-md-12">
    <div class="box">
        <div class="box-content">
            <div class="table-responsive">
            
            <form action="/admin/users" method="get">
                <div class="input-group">
                <input type="text" class="form-control" name="query" placeholder="Search users" value="{{ isset($_GET['query']) ? $_GET['query'] : '' }}">
                <div class="input-group-append">
                <button class="btn btn-secondary" type="submit" style="height: 50px;margin: 9px 0 0 0;"><i class="fa fa-search"></i>
                </button>
                </div>
                </div>
            </form>

                <table class="table table-hover no-margin table-bordered table-striped">
                    <thead>
                        <tr>

                            <th>Email</th>

                            <th>Signup Date</th>

                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $item)
                        <tr>
                            <td>{{ $item->email }}</td>
                            <td>{{ date('d-M-Y',strtotime($item->created_at)) }}</td>
                            <td>
                                @if($item->id != 1)
                                @if(have_premission(array(34)))
                                <a href="{{ url('/admin/users/'.$item->id.'/edit')}}"><i class="fa fa-edit fa-fw"></i></a>
                                @endif
                                @if(have_premission(array(35)))
                                {!! Form::open([
                                'method'=>'DELETE',
                                'url' => ['admin/users', $item->id],
                                'style' => 'display:inline'
                                ]) !!}
                                {!! Form::button('<i class="fa fa-trash fa-fw" title="Delete User"></i>', ['class' => 'delete-form-btn']) !!}
                                {!! Form::submit('Delete', ['class' => 'hidden deleteSubmit']) !!}
                                {!! Form::close() !!}
                                @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if (count($users) == 0)
                        <tr><td colspan="6"><div class="no-record-found alert alert-warning">No user found!</div></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <nav class="pull-right">{!! $users->render() !!}</nav>
        </div>
    </div>
</div>

@endsection
