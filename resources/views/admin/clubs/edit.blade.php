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
                    <div class="page-header"><h3>{!!$action!!} Club</h3></div>
                </div>
        
        <div class="box">
            <div class="box-content border">
                <form id="lg-form" enctype="multipart/form-data" class="form-horizontal form-validate" action="{{url('/admin/clubs')}}" method="POST" novalidate="novalidate">
                    <div class="form_wrap">
                        <div class="form-group">
                            <label class="control-label" for="name">Club Name</label>
                            <div class="">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter Name" data-rule-required="true" aria-required="true" value="{!!@$club['name']!!}"/>
                            </div>
                        </div>                    
                        
                        <div class="form-group">
                            <label class="control-label" for="name">Club Shirt</label>
                            <div class="">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-new thumbnail">
                                        @if(isset($club['club_shirt']) && ($club['club_shirt'] != ''))
                                        <img class="image-display" id="image_upload_preview" height="115px" src="{{URL::to('uploads/clubs/'.$club['club_shirt'])}}" />
                                        @else 
                                        <img class="image-display" id="image_upload_preview" height="115px" src="{{URL::to('frontend/images/default.png')}}" />
                                        @endif 
                                    </div>
                                    <div>
                                        <div class="clear5"></div>
                                        <input accept="image" class="image-input btn btn-info" type="file" id="inputFile" name='club_shirt'/>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="control-label" for="address">Address</label>
                            <div class="">
                                <textarea data-rule-required="true" aria-required="true" rows="5" class="form-control" name="address" id="address">{!!@$club['address']!!}</textarea>
                            </div>
                        </div>

                        
                        {{ csrf_field() }}
                        
                        <input class="image-input btn btn-info" type="hidden" name='action' value="{!!$action!!}"/>
                        <input class="image-input btn btn-info" type="hidden" name='id' value="{!!@$club['id']!!}"/>
                        
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-sm-offset-4 col-sm-12 text-right">
                                    <a href="{{url('/admin/clubs')}}" class="btn btn-default">Cancel</a>
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
<script>
      function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#image_upload_preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#inputFile").change(function () {
        readURL(this);
    });
</script>
@endsection
