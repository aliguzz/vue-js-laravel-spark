@extends('admin.layouts.app')

@section('content')

@include('admin.settings.subheader') 
<!-- Validation -->
<script src="{{ asset('js/plugins/validation/jquery.validate.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('css/plugins/select2/select2.css') }}">
<script src="{{ asset('js/plugins/select2/select2.min.js')}}"></script>



<div class="container-fluid">
    <div id="loading" style="display: none;"></div>
    <section class="inner-full-width-panel pr-30">
        <div class="tab-content">
            <div id="menu1" class="right-content-space fix-width">

                <div class="editor-domain-container-heading">
                    <div class="page-header"><h3>{!!$action!!} User</h3></div>
                </div>
        
        <div class="box">
            <div class="box-content border">
                <form id="user-form" class="form-horizontal form-validate" action="{{url('/admin/users')}}" method="POST" novalidate="novalidate">
                    <div class="form_wrap">
                        <input type="hidden" name="action" value="{!!$action!!}">
                        <input type="hidden" name="id" value="{!!@$user['id']!!}">
                        {{ csrf_field() }}
                        <!-- <div class="form-group">
                            <label class="control-label" for="title">Title</label>
                            <div class="">
                                <select required class="form-control" name="title" id="title">
                                    <option value="Mr" @if(isset($user['title']) && $user['title'] == 'Mr') selected @endif>Mr</option>
                                    <option value="Ms" @if(isset($user['title']) && $user['title'] == 'Ms') selected @endif>Ms</option>
                                    <option value="Mrs" @if(isset($user['title']) && $user['title'] == 'Mrs') selected @endif>Mrs</option>
                                    <option value="Dr" @if(isset($user['title']) && $user['title'] == 'Dr') selected @endif>Dr</option>
                                    <option value="Sir" @if(isset($user['title']) && $user['title'] == 'Sir') selected @endif>Sir</option>
                                </select>
                            </div>
                        </div> -->
                        <div class="form-group">
                            <label class="control-label" for="name">Full Name</label>
                            <div class="">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter Full Name" value="{!!@$user['name']!!}"/>
                            </div>
                        </div>
                       
                        
                        
                        
                      
                        <div class="form-group">
                            <label class="control-label" for="email">Email Address</label>
                            <div class=""><input type="text" class="form-control" name="email" id="email" placeholder="Enter Email" data-rule-email="true" data-rule-required="true" aria-required="true" value="{!!@$user['email']!!}"/></div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="phone">Phone Number</label>
                            <div class=""><input type="number" class="form-control" name="phone" id="phone" placeholder="Enter Telephone" data-rule-number="true" data-rule-minlength="10" value="{!!@$user['phone']!!}"/></div>
                        </div>
                        
                        <!-- <div class="form-group">
                            <label class="control-label pt_0" for="is_active">Status</label>
                            <div class="">
                                <input type="radio" name="is_active" value="1" @if(!isset($user['is_active']) || $user['is_active'] == 1) checked @endif /> Active &nbsp;&nbsp; <input type="radio" name="is_active" value="0"  @if(isset($user['is_active']) && $user['is_active'] == 0) checked @endif /> Inactive
                            </div>
                        </div> -->
                        
                        <h4 class="sub_heading">Login Credentials</h4>
                        <div class="form-group">
                            <label for="new_password" class="col-sm-12 control-label" for="new_password">New Password</label>
                            <div class="">
                                <input type="password" id="new_password" name="new_password" class='form-control' placeholder="Enter new password" @if($action == "Add") data-rule-required="true" aria-required="true" @endif data-rule-minlength="6" value=""/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="col-sm-12 control-label" for="confirm_password">Confirm Password</label>
                            <div class="">
                                <input type="password" id="confirm_password" name="confirm_password" class='form-control' placeholder="Retype new password" @if($action == "Add") data-rule-required="true" aria-required="true" @endif data-rule-equalto="#new_password" data-rule-minlength="6" value=""/>
                            </div>
                        </div>
                        
                        <div class="form-actions text-right">
                            <div class="control-label"></div>
                            <a href="{{url('/admin/users')}}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-preview">Continue</button>
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
    $("form").validate({
        rules: {
            first_name: { lettersonly: true }
            last_name: { lettersonly: true }
        }
    });
</script>
@endsection