@extends('admin.layouts.app')

@section('content')

<div class="breadcrumbs contentarea">
    <div class="container-fluid">
    <ul>
        <li>
            <a href="{{url('/admin/dashboard')}}">Dashboard</a>
            <i class="icon-angle-right"></i>
        </li>
        <li>
            <a>Categories</a>
        </li>
    </ul>
    <div class="close-bread">
        <a href="#"><i class="icon-remove"></i></a>
    </div>
</div>
</div>
<section class="contentarea">
    <div class="container-fluid">
        <div class="page-header"><h1>Categories <span class="badge">{{count($cats)}}</span>
                @if(have_premission(array(74)))
                <a href="{{url('/admin/categories/create')}}" class="btn btn-info pull-right">Add New Category</a>
                @endif
            </h1></div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-content">
                        <div class="table-responsive">
                            <table class="table table-hover table-nomargin no-margin table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
										<th>Category Title</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cats as $bg) 
                                    <tr>
                                        <td>{!!$bg->id!!}</td>
                                        <td>{!!$bg->title!!}</td>
                                        <td>@if($bg->status == 1) <label class="label label-success">Active</label> @else <label class="label label-danger">Inactive</label> @endif</td>
                                        <td>
                                            @if(have_premission(array(75)))
                                            <a href="{{ url('/admin/categories/'.$bg->id.'/edit')}}"><i class="fa fa-edit fa-fw"></i></a>
                                            @endif
                                            @if(have_premission(array(76)))
                                            {!! Form::open([
                                            'method'=>'DELETE',
                                            'url' => ['admin/categories', $bg->id],
                                            'style' => 'display:inline'
                                            ]) !!}
                                            {!! Form::button('<i class="fa fa-trash fa-fw" title="Delete Posts"></i>', ['class' => 'delete-form-btn']) !!}
                                            {!! Form::submit('Delete', ['class' => 'hidden deleteSubmit']) !!}
                                            {!! Form::close() !!}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if (count($cats) == 0)
                                    <tr><td colspan="7"><div class="no-record-found alert alert-warning">No Category found!</div></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                                   <nav class="pull-right">{!! $cats->render() !!}</nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection