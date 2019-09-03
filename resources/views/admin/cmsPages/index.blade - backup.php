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
                <a>CMS Pages</a>
            </li>
        </ul>
        <div class="close-bread">
            <a href="#"><i class="icon-remove"></i></a>
        </div>
    </div>
</div>

<section class="contentarea">
    <div class="container-fluid">
        <div class="page-header"><h1>CMS Pages <span class="badge">{{$total}}</span>
                @if(have_premission(45))
                <a href="{{url('/admin/cms-pages/create')}}" class="btn btn-info pull-right">Add New CMS Page</a>
                @endif
            </h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-content">
                        <div class="table-responsive">
                            <table class="table table-hover table-nomargin no-margin table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cmsPages as $cms) 
                                    <tr>
                                        <td>{!!$cms->title!!}</td>
                                        <td>{!!$cms->seo_url !!}</td>
                                        <td>@if($cms->is_active == 1) <label class="label label-success">Active</label> @else <label class="label label-danger">Inactive</label> @endif</td>
                                        <td>
                                            @if(have_premission(46))
                                            <a href="{{ url('/admin/cms-pages/'.$cms->id.'/edit')}}"><i class="fa fa-edit fa-fw"></i></a>
                                            @endif
                                            @if(have_premission(47))
                                            {!! Form::open([
                                            'method'=>'DELETE',
                                            'url' => ['admin/cms-pages', $cms->id],
                                            'style' => 'display:inline'
                                            ]) !!}
                                            {!! Form::button('<i class="fa fa-trash fa-fw" title="Delete CMS Page"></i>', ['class' => 'delete-form-btn']) !!}
                                            {!! Form::submit('Delete', ['class' => 'hidden deleteSubmit']) !!}
                                            {!! Form::close() !!}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if (count($cmsPages) == 0)
                                    <tr><td colspan="4"><div class="no-record-found alert alert-warning">No CMS page found!</div></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <nav class="pull-right">{!! $cmsPages->render() !!}</nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection