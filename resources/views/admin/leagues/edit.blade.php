@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader') 
<!-- Validation -->
<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>
<script src="{{ asset('js/plugins/jquery-ui/jquery.ui.spinner.js')}}"></script>


<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
        <div class="tab-content">
            <div id="menu1" class="right-content-space fix-width">

                <div class="editor-domain-container-heading">
                    <div class="page-header"><h3>{!!$action!!} League</h3></div>
                </div>
        
        <div class="box">
            <div class="box-content border">
                <form id="lg-form" enctype="multipart/form-data" class="form-horizontal form-validate" action="{{url('/admin/leagues')}}" method="POST" novalidate="novalidate">
                    <div class="form_wrap">
                        <div class="form-group">
                            <label class="control-label" for="league_code">League Code</label>
                            <div class="">
                                <input type="text" class="form-control" name="league_code" id="league_code" placeholder="Enter League Code" data-rule-required="true" aria-required="true" value="{!!@$league['league_code']!!}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="name">League Name</label>
                            <div class="">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter Name" data-rule-required="true" aria-required="true" value="{!!@$league['name']!!}"/>
                            </div>
                        </div>                    
                        {{ csrf_field() }}  
                        <input class="image-input btn btn-info" type="hidden" name='action' value="{!!$action!!}"/>
                        <input class="image-input btn btn-info" type="hidden" name='id' value="{!!@$league['id']!!}"/>
                        
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-sm-offset-4 col-sm-12 text-right">
                                    <a href="{{url('/admin/leagues')}}" class="btn btn-default">Cancel</a>
                                    <button type="submit" class="btn btn-preview">Continue</button>
                                </div>
                            </div>    
                        </div>
                    </div>    
                </form>
            </div>
        </div>
            </div>
        </div>
    </section>
</div>
@endsection
